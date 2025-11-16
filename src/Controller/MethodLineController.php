<?php

namespace App\Controller;

use App\Entity\Argument;
use App\Entity\Condition;
use App\Entity\ConditionGroup;
use App\Entity\Method;
use App\Entity\MethodLine;
use App\Entity\Place;
use App\Enum\OperatorType;
use App\Repository\MethodLineRepository;
use App\Service\SnapshotManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;


#[Route('/api/engine/methodLine')]
#[OA\Tag(name: 'MethodLine')]
class MethodLineController extends ApiInterface
{


    #[Route('/list', methods: ['GET'])]
    #[OA\Get(
        path: "/api/engine/methodLine/list",
        summary: "Lister les lignes de méthodes",
        description: "Retourne la liste de toutes les lignes de méthodes",
        tags: ['MethodLine'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des lignes de méthodes récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique de la ligne de méthode"),
                            new OA\Property(property: "name", type: "string", example: "Ligne de méthode", description: "Nom de la ligne de méthode"),
                            new OA\Property(property: "code", type: "string", example: "LIGNE_METHOD", description: "Code unique de la ligne de méthode"),
                            new OA\Property(property: "method", type: "integer", example: 1, description: "ID de la méthode associée")
                        ]
                    )
                )
            )
        ]
    )]
    public function list()
    {
        $all = $this->em->getRepository(MethodLine::class)->findAll();
        return $this->responseData($all, 'method', true);
    }


    #[Route('/Method/{id}', methods: ['GET'])]
    #[OA\Get(
        path: "/api/engine/methodLine/Method/{id}",
        summary: "Lister les lignes de méthodes",
        description: "Retourne la liste de toutes les lignes de méthodes",
        tags: ['MethodLine'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des lignes de méthodes récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique de la ligne de méthode"),
                            new OA\Property(property: "name", type: "string", example: "Ligne de méthode", description: "Nom de la ligne de méthode"),
                            new OA\Property(property: "code", type: "string", example: "LIGNE_METHOD", description: "Code unique de la ligne de méthode"),
                            new OA\Property(property: "method", type: "integer", example: 1, description: "ID de la méthode associée")
                        ]
                    )
                )
            )
        ]
    )]
    public function getMethodeLineOfMethod(int $id)
    {
        $all = $this->em->getRepository(MethodLine::class)->findBy(['method' => $id]);
        return $this->responseData($all, 'methodLine', true);
    }

    #[Route('/new/{id}/line/full', methods: ['POST'])]
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
    public function addLineFull(int $id, Request $r, SnapshotManager $snapshot): JsonResponse
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
                $cond->setGroupCondition($group);
                $cond->setOrderIndex($c['order'] ?? $j + 1);
                if (isset($c['leftArgId'])) {
                    $cond->setLeftArgument($this->em->find(Argument::class, $c['leftArgId']));
                }
                $cond->setOperator(OperatorType::from($c['op'] ?? '='));
                $cond->setRightValue($c['right'] ?? null);
                $this->em->persist($cond);
            }
        }

        $snapshot->createSnapshot($line);
        $this->em->flush();

        return $this->json(['id' => $line->getId()]);
    }

    
    #[Route('/{id}/line/full', methods: ['PUT'])]
    #[OA\Put(summary: "Mettre à jour une ligne complète avec places et conditions")]
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
    #[OA\Response(response: 200, description: "Ligne complète mise à jour avec succès")]
    public function updateLineFull(int $id, Request $request, SnapshotManager $snapshotManager): JsonResponse
    {
        // Récupérer la ligne existante
        $line = $this->em->find(MethodLine::class, $id);
        if (!$line) {
            return $this->json(['error' => 'MethodLine not found'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        // Démarrer une transaction pour assurer l'intégrité des données
        $this->em->beginTransaction();

        try {
            // Mettre à jour les propriétés de base de la ligne
            $line->setOrderIndex($data['orderIndex'] ?? $line->getOrderIndex());
            $line->setResultVariable($data['resultVariable'] ?? $line->getResultVariable());
            $line->setExpression($data['expression'] ?? $line->getExpression());
            $line->setLineType($data['lineType'] ?? $line->getLineType());
            $line->setMetadata($data['metadata'] ?? $line->getMetadata());

            // Supprimer les places existantes
            $existingPlaces = $this->em->getRepository(Place::class)->findBy(['line' => $line]);
            foreach ($existingPlaces as $place) {
                $this->em->remove($place);
            }

            // Recréer les places avec les nouvelles données
            foreach ($data['places'] ?? [] as $i => $placeData) {
                $place = new Place();
                $place->setLine($line);
                $place->setOrderIndex($placeData['order'] ?? $i + 1);
                $place->setLiteralValue($placeData['val'] ?? null);

                if (isset($placeData['op'])) {
                    $place->setOperator(OperatorType::from($placeData['op']));
                }

                if (isset($placeData['argId'])) {
                    $arg = $this->em->find(Argument::class, $placeData['argId']);
                    if ($arg) {
                        $place->setArgument($arg);
                    }
                }

                $this->em->persist($place);
            }

            // Supprimer les groupes de conditions existants
            $existingGroups = $this->em->getRepository(ConditionGroup::class)->findBy(['line' => $line]);
            foreach ($existingGroups as $group) {
                $this->em->remove($group);
            }

            // Recréer les groupes de conditions avec les nouvelles données
            foreach ($data['groups'] ?? [] as $groupData) {
                $group = new ConditionGroup();
                $group->setLine($line);
                $group->setLogicOperator($groupData['logic'] ?? 'AND');
                $group->setOrderIndex($groupData['order'] ?? 1);
                $this->em->persist($group);
                $this->em->flush(); // Flush pour obtenir l'ID du groupe

                // Créer les conditions pour ce groupe
                foreach ($groupData['conditions'] ?? [] as $j => $conditionData) {
                    $condition = new Condition();
                    $condition->setGroupCondition($group);
                    $condition->setOrderIndex($conditionData['order'] ?? $j + 1);

                    if (isset($conditionData['leftArgId'])) {
                        $leftArg = $this->em->find(Argument::class, $conditionData['leftArgId']);
                        if ($leftArg) {
                            $condition->setLeftArgument($leftArg);
                        }
                    }

                    $condition->setOperator(OperatorType::from($conditionData['op'] ?? '='));
                    $condition->setRightValue($conditionData['right'] ?? null);
                    $this->em->persist($condition);
                }
            }

            // Créer un snapshot de la version mise à jour
            $snapshotManager->createSnapshot($line);

            // Commit de la transaction
            $this->em->commit();
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Ligne mise à jour avec succès',
                'id' => $line->getId(),
                'methodId' => $line->getMethod()->getId()
            ]);
        } catch (\Exception $e) {
            // Rollback en cas d'erreur
            $this->em->rollback();

            return $this->json([
                'error' => 'Erreur lors de la mise à jour de la ligne',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
