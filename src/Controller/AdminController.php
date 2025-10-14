<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;

use App\Entity\{
    EngineBranch,
    Method,
    MethodLine,
    Place,
    Argument,
    Condition,
    ConditionGroup,
    MethodLineVersion
};
use App\Enum\{EngineStatus, OperatorType};
use App\Service\SnapshotManager;

#[Route('/api/admin')]
#[OA\Tag(name: 'admin')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SnapshotManager $snapshots
    ) {}

    #[Route('/engine', methods: ['POST'])]
    #[OA\Post(summary: "Créer un moteur (EngineBranch)")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "name", type: "string", example: "Moteur Santé"),
                new OA\Property(property: "branch", type: "integer", example: 1),
                new OA\Property(property: "description", type: "string", nullable: true, example: "Moteur principal de calcul santé"),
                new OA\Property(property: "status", type: "string", enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"], example: "ACTIVE")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Moteur créé avec succès")]
    public function createEngine(Request $r): JsonResponse
    {
        $d = json_decode($r->getContent(), true) ?? [];

        $e = new EngineBranch();
        $e->setName($d['name'] ?? 'Engine');
        $e->setBranch((int)($d['branch'] ?? 0));
        $e->setDescription($d['description'] ?? null);
        $e->setStatus(EngineStatus::from($d['status'] ?? 'ACTIVE'));

        $this->em->persist($e);
        $this->em->flush();

        return $this->json(['id' => $e->getId(), 'message' => 'Engine created']);
    }

    #[Route('/method', methods: ['POST'])]
    #[OA\Post(summary: "Créer une méthode associée à un moteur")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "name", type: "string", example: "Calcul Prime Auto"),
                new OA\Property(property: "code", type: "string", example: "CALC_PRIME_AUTO"),
                new OA\Property(property: "engineId", type: "integer", example: 1),
                new OA\Property(property: "insurerId", type: "integer", nullable: true, example: 10),
                new OA\Property(property: "description", type: "string", nullable: true, example: "Méthode pour le calcul de prime auto")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Méthode créée avec succès")]
    public function createMethod(Request $r): JsonResponse
    {
        $d = json_decode($r->getContent(), true) ?? [];

        $method = new Method();
        $method->setName($d['name']);
        $method->setCode($d['code']);
        $method->setDescription($d['description'] ?? null);

        if (isset($d['engineId'])) {
            $engine = $this->em->find(EngineBranch::class, $d['engineId']);
            $method->setEngine($engine);
        }

        if (isset($d['insurerId'])) {
            $method->setInsurer((int)$d['insurerId']);
        }

        $this->em->persist($method);
        $this->em->flush();

        return $this->json(['id' => $method->getId(), 'message' => 'Method created']);
    }

    #[Route('/method/{id}/line', methods: ['POST'])]
    #[OA\Post(summary: "Ajouter une ligne à une méthode")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "orderIndex", type: "integer", example: 1),
                new OA\Property(property: "resultVariable", type: "string", example: "PRIME_NETTE"),
                new OA\Property(property: "expression", type: "string", example: "(PRIME_BRUTE - REDUCTION)"),
                new OA\Property(property: "lineType", type: "string", example: "calculation"),
                new OA\Property(property: "metadata", type: "object", nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Ligne ajoutée avec succès")]
    public function addLine(int $id, Request $r): JsonResponse
    {
        $method = $this->em->find(Method::class, $id);
        if (!$method) return $this->json(['error' => 'Method not found'], 404);

        $d = json_decode($r->getContent(), true) ?? [];

        $line = new MethodLine();
        $line->setMethod($method);
        $line->setOrderIndex($d['orderIndex'] ?? 1);
        $line->setResultVariable($d['resultVariable'] ?? null);
        $line->setExpression($d['expression'] ?? null);
        $line->setLineType($d['lineType'] ?? 'calculation');
        $line->setMetadata($d['metadata'] ?? null);

        $this->em->persist($line);
        $this->em->flush();

        $this->snapshots->createSnapshot($line);
        $this->em->flush();

        return $this->json(['id' => $line->getId(), 'methodId' => $method->getId()]);
    }

    #[Route('/method/{id}/line/full', methods: ['POST'])]
    #[OA\Post(summary: "Ajouter une ligne complète avec places et conditions")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "orderIndex", type: "integer", example: 1),
                new OA\Property(property: "resultVariable", type: "string", example: "PRIME_TOTALE"),
                new OA\Property(property: "expression", type: "string", example: "PRIME_BASE + TAXE"),
                new OA\Property(property: "lineType", type: "string", example: "calculation"),
                new OA\Property(property: "places", type: "array", items: new OA\Items(
                    type: "object",
                    properties: [
                        new OA\Property(property: "order", type: "integer", example: 1),
                        new OA\Property(property: "val", type: "string", example: "100"),
                        new OA\Property(property: "op", type: "string", example: "+"),
                        new OA\Property(property: "argId", type: "integer", example: 12)
                    ]
                )),
                new OA\Property(property: "groups", type: "array", items: new OA\Items(
                    type: "object",
                    properties: [
                        new OA\Property(property: "logic", type: "string", example: "AND"),
                        new OA\Property(property: "order", type: "integer", example: 1),
                        new OA\Property(property: "conditions", type: "array", items: new OA\Items(
                            type: "object",
                            properties: [
                                new OA\Property(property: "order", type: "integer", example: 1),
                                new OA\Property(property: "leftArgId", type: "integer", example: 5),
                                new OA\Property(property: "op", type: "string", example: ">="),
                                new OA\Property(property: "right", type: "string", example: "18")
                            ]
                        ))
                    ]
                ))
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Ligne complète ajoutée avec succès")]
    public function addLineFull(int $id, Request $r): JsonResponse
    {
        $method = $this->em->find(Method::class, $id);
        if (!$method) return $this->json(['error' => 'Method not found'], 404);

        $d = json_decode($r->getContent(), true) ?? [];

        $line = new MethodLine();
        $line->setMethod($method);
        $line->setOrderIndex($d['orderIndex'] ?? 1);
        $line->setResultVariable($d['resultVariable'] ?? null);
        $line->setExpression($d['expression'] ?? null);
        $line->setLineType($d['lineType'] ?? 'calculation');
        $line->setMetadata($d['metadata'] ?? null);
        $this->em->persist($line);
        $this->em->flush();

        foreach ($d['places'] ?? [] as $i => $p) {
            $place = new Place();
            $place->setLine($line);
            $place->setOrderIndex($p['order'] ?? $i + 1);
            $place->setLiteralValue($p['val'] ?? null);
            if (isset($p['op'])) $place->setOperator(OperatorType::from($p['op']));
            if (isset($p['argId'])) {
                $arg = $this->em->find(Argument::class, $p['argId']);
                $place->setArgument($arg);
            }
            $this->em->persist($place);
        }

        foreach ($d['groups'] ?? [] as $g) {
            $group = new ConditionGroup();
            $group->setLine($line);
            $group->setLogicOperator($g['logic'] ?? 'AND');
            $group->setOrderIndex($g['order'] ?? 1);
            $this->em->persist($group);
            $this->em->flush();

            foreach ($g['conditions'] ?? [] as $j => $c) {
                $cond = new Condition();
                $cond->setGroup($group);
                $cond->setOrderIndex($c['order'] ?? $j + 1);
                if (isset($c['leftArgId'])) {
                    $cond->setLeftArgument($this->em->find(Argument::class, $c['leftArgId']));
                }
                $cond->setOperator(OperatorType::from($c['op'] ?? '='));
                $cond->setRightValue($c['right'] ?? null);
                $this->em->persist($cond);
            }
        }

        $this->snapshots->createSnapshot($line);
        $this->em->flush();

        return $this->json(['id' => $line->getId()]);
    }

    #[Route('/line/{id}/snapshots', methods: ['GET'])]
    #[OA\Get(summary: "Lister les snapshots d'une ligne")]
    #[OA\Response(response: 200, description: "Liste des snapshots renvoyée")]
    public function listSnapshots(int $id): JsonResponse
    {
        $line = $this->em->find(MethodLine::class, $id);
        if (!$line) return $this->json(['error' => 'line not found'], 404);

        $snaps = $this->em->getRepository(MethodLineVersion::class)
            ->findBy(['line' => $line], ['versionNumber' => 'DESC']);

        return $this->json(array_map(fn($s) => [
            'version' => $s->getVersionNumber(),
            'snapshot' => $s->getSnapshotJson(),
            'createdAt' => $s->getCreatedAt()?->format('c'),
        ], $snaps));
    }

    #[Route('/line/{id}/rollback/{version}', methods: ['POST'])]
    #[OA\Post(summary: "Restaurer une version précédente d'une ligne")]
    #[OA\Response(response: 200, description: "Version restaurée avec succès")]
    public function rollbackLine(int $id, int $version): JsonResponse
    {
        $line = $this->em->find(MethodLine::class, $id);
        if (!$line) return $this->json(['error' => 'line not found'], 404);

        $snap = $this->em->getRepository(MethodLineVersion::class)
            ->findOneBy(['line' => $line, 'versionNumber' => $version]);
        if (!$snap) return $this->json(['error' => 'snapshot not found'], 404);

        $data = $snap->getSnapshotJson();
        $line->setOrderIndex($data['line']['orderIndex']);
        $line->setResultVariable($data['line']['resultVariable']);
        $line->setLineType($data['line']['lineType']);
        $line->setExpression($data['line']['expression']);
        $line->setMetadata($data['line']['metadata'] ?? null);
        $this->em->flush();

        return $this->json(['restored' => true, 'lineId' => $id, 'version' => $version]);
    }
}
