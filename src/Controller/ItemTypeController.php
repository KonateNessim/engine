<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ItemType;
use OpenApi\Attributes as OA;

#[Route('/api/admin/itemtype')]
#[OA\Tag(name: 'ItemType')]
class ItemTypeController extends AbstractController
{
  public function __construct(private EntityManagerInterface $em) {}

  #[Route('', methods: ['GET'])]
  #[OA\Get(
    summary: "Lister tous les ItemType",
    description: "Retourne la liste de tous les ItemType disponibles",
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des ItemType",
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
    $all = $this->em->getRepository(ItemType::class)->findAll();
    return $this->json(array_map(fn($e) => ['id' => $e->getId()], $all));
  }

  #[Route('/{id}', methods: ['GET'])]
  #[OA\Get(
    summary: "Afficher un ItemType",
    description: "Retourne les détails d’un ItemType par son identifiant",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    responses: [
      new OA\Response(
        response: 200,
        description: "ItemType trouvé",
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
    $e = $this->em->find(ItemType::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    return $this->json(['id' => $e->getId()]);
  }

  #[Route('', methods: ['POST'])]
  #[OA\Post(
    summary: "Créer un ItemType",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "name", type: "string", example: "Item A")
        ]
      )
    ),
    responses: [
      new OA\Response(
        response: 201,
        description: "ItemType créé",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "id", type: "integer", example: 3)
          ]
        )
      )
    ]
  )]
  public function create(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];
    $e = new ItemType();
    $this->apply($e, $d);
    $this->em->persist($e);
    $this->em->flush();
    return $this->json(['id' => $e->getId()], 201);
  }

  #[Route('/{id}', methods: ['PUT'])]
  #[OA\Put(
    summary: "Mettre à jour un ItemType",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "name", type: "string", example: "Nouveau Item")
        ]
      )
    ),
    responses: [
      new OA\Response(
        response: 200,
        description: "ItemType mis à jour",
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
  public function update(int $id, Request $r): JsonResponse
  {
    $e = $this->em->find(ItemType::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    $d = json_decode($r->getContent(), true) ?? [];
    $this->apply($e, $d);
    $this->em->flush();
    return $this->json(['id' => $e->getId()]);
  }

  #[Route('/{id}', methods: ['DELETE'])]
  #[OA\Delete(
    summary: "Supprimer un ItemType",
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
    $e = $this->em->find(ItemType::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    $this->em->remove($e);
    $this->em->flush();
    return $this->json(['deleted' => true]);
  }

  private function apply(ItemType $e, array $d): void
  {
    foreach ($d as $field => $val) {
      switch ($field) {
        case 'name':
          $e->setName($val);
          break;
        default:
          break;
      }
    }
  }
}
