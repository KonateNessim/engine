<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{Argument, Method, VersionMethod, MethodLine, Place, ConditionGroup, Condition, EngineBranch};
use App\Enum\EngineStatus;
use OpenApi\Attributes as OA;

#[Route('/api/admin')]
#[OA\Tag(name: 'admin')]
class AdminController extends AbstractController
{
  public function __construct(private EntityManagerInterface $em) {}

  #[Route('/engine', methods: ['POST'])]
  #[OA\Post(
    path: "/admin/engine",
    summary: "Créer un moteur",
    description: "Crée un nouvel EngineBranch",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "name", type: "string"),
          new OA\Property(property: "branch", type: "integer"),
          new OA\Property(property: "description", type: "string", nullable: true),
          new OA\Property(property: "status", type: "string", enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"])
        ]
      )
    ),
    responses: [
      new OA\Response(response: 200, description: "Identifiant du moteur créé")
    ]
  )]
  public function createEngine(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];
    $e = new EngineBranch();
    $e->setName($d['name'] ?? 'Engine');
    $e->setDescription($d['description'] ?? null);
    $e->setBranch((int)($d['branch'] ?? null));
    $e->setStatus(EngineStatus::from($d['status'] ?? 'DRAFT'));
    $this->em->persist($e);
    $this->em->flush();
    return $this->json(['id' => $e->getId()]);
  }

  

  #[Route('/method', methods: ['POST'])]
  #[OA\Post(
    path: "/admin/method",
    summary: "Créer une méthode",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "name", type: "string"),
          new OA\Property(property: "code", type: "string"),
          new OA\Property(property: "description", type: "string", nullable: true),
          new OA\Property(property: "returnType", type: "string", example: "float"),
          new OA\Property(property: "engineId", type: "integer", nullable: true),
          new OA\Property(property: "insurerId", type: "integer", nullable: true),
        ]
      )
    ),
    responses: [
      new OA\Response(response: 200, description: "Identifiant de la méthode créée")
    ]
  )]
  public function createMethod(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];
    $m = new Method();
    $m->setName($d['name']);
    $m->setCode($d['code']);
    $m->setDescription($d['description'] ?? null);
    $m->setReturnType($d['returnType'] ?? 'float');
    if (isset($d['engineId'])) $m->setEngine($this->em->find(EngineBranch::class, (int)$d['engineId']));
    if (isset($d['insurerId'])) $m->setInsurer((int)$d['insurerId']);
    $this->em->persist($m);
    $this->em->flush();
    return $this->json(['id' => $m->getId()]);
  }

  #[Route('/version-method', methods: ['POST'])]
  #[OA\Post(
    path: "/admin/version-method",
    summary: "Créer une version de méthode",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "methodId", type: "integer"),
          new OA\Property(property: "versionNumber", type: "string", example: "v1"),
          new OA\Property(property: "isActive", type: "boolean", example: true),
        ]
      )
    ),
    responses: [
      new OA\Response(response: 200, description: "Identifiant de la version créée")
    ]
  )]
  public function createVersionMethod(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];
    $vm = new VersionMethod();
    $vm->setMethod($this->em->find(Method::class, (int)$d['methodId']));
    $vm->setVersionNumber($d['versionNumber'] ?? 'v1');
    $vm->setIsActive((bool)($d['isActive'] ?? true));
    $this->em->persist($vm);
    $this->em->flush();
    return $this->json(['id' => $vm->getId()]);
  }

  #[Route('/version-method/{id}/line', methods: ['POST'])]
  #[OA\Post(
    path: "/admin/version-method/{id}/line",
    summary: "Ajouter une ligne à une version de méthode",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "orderIndex", type: "integer", example: 1),
          new OA\Property(property: "resultVariable", type: "string"),
          new OA\Property(property: "expression", type: "string"),
          new OA\Property(property: "lineType", type: "string", example: "calculation"),
          new OA\Property(property: "metadata", type: "string", nullable: true),
        ]
      )
    ),
    responses: [
      new OA\Response(response: 200, description: "Ligne ajoutée avec succès")
    ]
  )]
  public function addLine(int $id, Request $r): JsonResponse
  {
    $vm = $this->em->find(VersionMethod::class, $id);
    if (!$vm) return $this->json(['error' => 'vm not found'], 404);
    $d = json_decode($r->getContent(), true) ?? [];
    $ln = new MethodLine();
    $ln->setVersionMethod($vm);
    $ln->setOrderIndex((int)($d['orderIndex'] ?? 1));
    $ln->setResultVariable($d['resultVariable'] ?? null);
    $ln->setExpression($d['expression'] ?? null);
    $ln->setLineType($d['lineType'] ?? 'calculation');
    $ln->setMetadata($d['metadata'] ?? null);
    $this->em->persist($ln);
    $this->em->flush();
    return $this->json(['id' => $ln->getId(), 'versionMethodId' => $vm->getId()]);
  }

  #[Route('/version-method/{id}/line/full', methods: ['POST'])]
  #[OA\Post(
    path: "/admin/version-method/{id}/line/full",
    summary: "Ajouter une ligne complète (places et conditions) à une version de méthode",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "orderIndex", type: "integer"),
          new OA\Property(property: "resultVariable", type: "string"),
          new OA\Property(property: "expression", type: "string"),
          new OA\Property(property: "lineType", type: "string"),
          new OA\Property(property: "metadata", type: "string", nullable: true),
          new OA\Property(
            property: "places",
            type: "array",
            items: new OA\Items(
              type: "object",
              properties: [
                new OA\Property(property: "order", type: "integer"),
                new OA\Property(property: "val", type: "string"),
                new OA\Property(property: "op", type: "string"),
                new OA\Property(property: "argId", type: "integer"),
              ]
            )
          ),
          new OA\Property(
            property: "groups",
            type: "array",
            items: new OA\Items(
              type: "object",
              properties: [
                new OA\Property(property: "logic", type: "string", example: "AND"),
                new OA\Property(property: "order", type: "integer"),
                new OA\Property(
                  property: "conditions",
                  type: "array",
                  items: new OA\Items(
                    type: "object",
                    properties: [
                      new OA\Property(property: "order", type: "integer"),
                      new OA\Property(property: "leftArgId", type: "integer"),
                      new OA\Property(property: "op", type: "string", example: "="),
                      new OA\Property(property: "right", type: "string"),
                    ]
                  )
                )
              ]
            )
          )
        ]
      )
    ),
    responses: [
      new OA\Response(response: 200, description: "Ligne complète ajoutée avec succès")
    ]
  )]
  public function addLineFull(int $id, Request $r): JsonResponse
  {
    $vm = $this->em->find(VersionMethod::class, $id);
    if (!$vm) return $this->json(['error' => 'vm not found'], 404);
    $d = json_decode($r->getContent(), true) ?? [];
    $ln = new MethodLine();
    $ln->setVersionMethod($vm);
    $ln->setOrderIndex((int)($d['orderIndex'] ?? 1));
    $ln->setResultVariable($d['resultVariable'] ?? null);
    $ln->setExpression($d['expression'] ?? null);
    $ln->setLineType($d['lineType'] ?? 'calculation');
    $ln->setMetadata($d['metadata'] ?? null);
    $this->em->persist($ln);
    $this->em->flush();
    foreach (($d['places'] ?? []) as $i => $p) {
      $pl = new Place();
      $pl->setLine($ln);
      $pl->setOrderIndex((int)($p['order'] ?? $i + 1));
      $pl->setLiteralValue($p['val'] ?? null);
      if (isset($p['op'])) $pl->setOperator(\App\Enum\OperatorType::from($p['op']));
      if (isset($p['argId'])) $pl->setArgument($this->em->find(Argument::class, (int)$p['argId']));
      $this->em->persist($pl);
    }
    foreach (($d['groups'] ?? []) as $g) {
      $gr = new ConditionGroup();
      $gr->setLine($ln);
      $gr->setLogicOperator(strtoupper($g['logic'] ?? 'AND'));
      $gr->setOrderIndex((int)($g['order'] ?? 1));
      $this->em->persist($gr);
      $this->em->flush();
      foreach (($g['conditions'] ?? []) as $j => $c) {
        $co = new Condition();
        $co->setGroup($gr);
        $co->setOrderIndex((int)($c['order'] ?? $j + 1));
        if (isset($c['leftArgId'])) $co->setLeftArgument($this->em->find(Argument::class, (int)$c['leftArgId']));
        $co->setOperator(\App\Enum\OperatorType::from($c['op'] ?? '='));
        $co->setRightValue($c['right'] ?? null);
        $this->em->persist($co);
      }
    }
    $this->em->flush();
    return $this->json(['id' => $ln->getId()]);
  }
}
