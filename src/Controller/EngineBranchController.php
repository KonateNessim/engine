<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EngineBranch;
use App\Enum\EngineStatus;
use OpenApi\Attributes as OA;

#[Route('/api/engine/engineBranch')]
#[OA\Tag(name: 'EngineBranch')]
class EngineBranchController extends ApiInterface
{
  //public function __construct(private EntityManagerInterface $em) {}

  #[Route('', methods: ['GET'])]
  #[OA\Get(
    path: "/admin/engineBranch",
    summary: "Lister les moteurs",
    description: "Retourne la liste des EngineBranch disponibles",
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des EngineBranch",
        content: new OA\JsonContent(
          type: "array",
          items: new OA\Items(
            type: "object",
            properties: [
              new OA\Property(property: "id", type: "integer", example: 1)
            ]
          )
        )
      )
    ]
  )]
  public function list(): JsonResponse
  {
    $all = $this->em->getRepository(EngineBranch::class)->findAll();
    return $this->responseData($all, 'method', true);
  }

  #[Route('/{id}', methods: ['GET'])]
  #[OA\Get(
    path: "/admin/engineBranch/{id}",
    summary: "Détails d’un moteur",
    description: "Retourne les informations d’un EngineBranch par son identifiant",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    responses: [
      new OA\Response(
        response: 200,
        description: "Moteur trouvé",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "id", type: "integer", example: 1)
          ]
        )
      ),
      new OA\Response(response: 404, description: "Non trouvé")
    ]
  )]
  public function detail(int $id): JsonResponse
  {
    $e = $this->em->find(EngineBranch::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    return $this->json(['id' => $e->getId()]);
  }

  #[Route('/create/{branch}', methods: ['POST'])]
  #[OA\Post(
    path: "/engineBranch/{branch}",
    summary: "Créer un moteur",
    description: "Crée un nouvel EngineBranch",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "name", type: "string", example: "Main Engine"),
          new OA\Property(property: "description", type: "string", nullable: true, example: "Moteur principal"),
          new OA\Property(property: "branch", type: "integer", example: 101),
          new OA\Property(
            property: "status",
            type: "string",
            enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"],
            example: "DRAFT"
          )
        ]
      )
    ),
    responses: [
      new OA\Response(
        response: 201,
        description: "Moteur créé",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "id", type: "integer", example: 1)
          ]
        )
      )
    ]
  )]
  public function createEngine(Request $r, string $branch): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];
    $e = new EngineBranch();
    $e->setName($d['name'] ?? 'Engine');
    $e->setDescription($d['description'] ?? null);
    $e->setBranch(($d['branch']));
    $e->setStatus(EngineStatus::from($d['status'] ?? 'DRAFT'));
    $this->em->persist($e);
    $this->em->flush();
    return $this->json(['id' => $e->getId()], 201);
  }

  #[Route('/{id}', methods: ['PUT'])]
  #[OA\Put(
    path: "/admin/engineBranch/{id}",
    summary: "Mettre à jour un moteur",
    description: "Met à jour les informations d’un EngineBranch existant",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "name", type: "string"),
          new OA\Property(property: "description", type: "string", nullable: true),
          new OA\Property(property: "branch", type: "integer"),
          new OA\Property(
            property: "status",
            type: "string",
            enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"]
          )
        ]
      )
    ),
    responses: [
      new OA\Response(
        response: 200,
        description: "Moteur mis à jour",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(property: "updated", type: "boolean", example: true)
          ]
        )
      ),
      new OA\Response(response: 404, description: "Non trouvé")
    ]
  )]
  public function update(int $id, Request $r): JsonResponse
  {
    $e = $this->em->find(EngineBranch::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    $d = json_decode($r->getContent(), true) ?? [];
    $this->apply($e, $d);
    $this->em->flush();
    return $this->json(['id' => $e->getId(), 'updated' => true]);
  }

  #[Route('/{id}', methods: ['DELETE'])]
  #[OA\Delete(
    path: "/admin/engineBranch/{id}",
    summary: "Supprimer un moteur",
    description: "Supprime un EngineBranch par son identifiant",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    responses: [
      new OA\Response(
        response: 200,
        description: "Suppression réussie",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "deleted", type: "boolean", example: true)
          ]
        )
      ),
      new OA\Response(response: 404, description: "Non trouvé")
    ]
  )]
  public function delete(int $id): JsonResponse
  {
    $e = $this->em->find(EngineBranch::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    $this->em->remove($e);
    $this->em->flush();
    return $this->json(['deleted' => true]);
  }

  private function apply(EngineBranch $e, array $d): void
  {
    foreach ($d as $field => $val) {
      switch ($field) {
        case 'name':
          $e->setName($val);
          break;
        case 'description':
          $e->setDescription($val);
          break;
        case 'branch':
          $e->setBranch((int)$val);
          break;
        case 'status':
          $e->setStatus(EngineStatus::from($val));
          break;
        default:
          break;
      }
    }
  }
}
