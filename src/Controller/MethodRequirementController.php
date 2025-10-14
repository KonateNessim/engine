<?php

namespace App\Controller;

use App\Entity\Argument;
use App\Entity\DataType;
use App\Entity\ItemType;
use App\Entity\Method;
use App\Entity\MethodRequirement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;

#[Route('/api/admin/requirement')]
#[OA\Tag(name: 'MethodRequirement')]
class MethodRequirementController extends ApiInterface
{
  //public function __construct(private EntityManagerInterface $em) {}

  #[Route('', methods: ['GET'])]
  #[OA\Get(
    path: "/api/admin/requirement",
    summary: "Lister les MethodRequirement",
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des MethodRequirement",
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
    $all = $this->em->getRepository(MethodRequirement::class)->findAll();
    return $this->responseData($all, 'group_requirements', true);
  }

  #[Route('/{id}', methods: ['GET'])]
  #[OA\Get(
    path: "/api/admin/requirement/{id}",
    summary: "Détails d’un MethodRequirement",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    responses: [
      new OA\Response(
        response: 200,
        description: "MethodRequirement trouvé",
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
    $e = $this->em->find(MethodRequirement::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    return $this->json(['id' => $e->getId()]);
  }

  #[Route('/create', methods: ['POST'])]
  #[OA\Post(
    path: "/api/admin/requirement",
    summary: "Créer un MethodRequirement",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "label", type: "string", example: "Durée du prêt"),
          new OA\Property(property: "code", type: "string", example: "DUREE"),
          new OA\Property(property: "isRequired", type: "boolean", example: false),
          new OA\Property(property: "validationRules", type: "object", example: ["min" => 18, "max" => 80]),
          new OA\Property(property: "defaultValue", type: "string", example: "0"),
          new OA\Property(property: "method", type: "integer", example: 1, description: "ID de la méthode"),
        ]
      )
    ),
    responses: [
      new OA\Response(
        response: 201,
        description: "MethodRequirement créé",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "id", type: "integer", example: 5)
          ]
        )
      )
    ]
  )]
  public function create(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];
    $e = new MethodRequirement();
    $this->apply($e, $d);
    $this->em->persist($e);
    $this->em->flush();
    return $this->json(['id' => $e->getId()], 201);
  }

  #[Route('/{id}', methods: ['PUT'])]
  #[OA\Put(
    path: "/api/admin/requirement/{id}",
    summary: "Mettre à jour un MethodRequirement",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "method", type: "integer", example: 1),
          new OA\Property(property: "label", type: "string", example: "Durée du prêt"),
          new OA\Property(property: "code", type: "string", example: "DUREE"),
          new OA\Property(property: "isRequired", type: "boolean", example: false),
          new OA\Property(property: "validationRules", type: "object", example: ["min" => 18, "max" => 80]),
          new OA\Property(property: "defaultValue", type: "string", example: "0"),


        ]
      )
    ),
    responses: [
      new OA\Response(
        response: 200,
        description: "MethodRequirement mis à jour",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "id", type: "integer", example: 5),
            new OA\Property(property: "updated", type: "boolean", example: true)
          ]
        )
      ),
      new OA\Response(response: 404, description: "Non trouvé")
    ]
  )]
  public function update(int $id, Request $r): JsonResponse
  {
    $e = $this->em->find(MethodRequirement::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    $d = json_decode($r->getContent(), true) ?? [];
    $this->apply($e, $d);
    $this->em->flush();
    return $this->json(['id' => $e->getId(), 'updated' => true]);
  }

  #[Route('/{id}', methods: ['DELETE'])]
  #[OA\Delete(
    path: "/api/admin/requirement/{id}",
    summary: "Supprimer un MethodRequirement",
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
    $e = $this->em->find(MethodRequirement::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    $this->em->remove($e);
    $this->em->flush();
    return $this->json(['deleted' => true]);
  }

  private function apply(MethodRequirement $e, array $d): void
  {
    foreach ($d as $field => $val) {
      switch ($field) {
        case 'method':
          $e->setMethod($this->em->find(Method::class, (int)$val));
          break;
        case 'label':
          $e->setLabel($val);
          break;
        case 'code':
          $e->setCode($this->em->find(Argument::class, (int)$val)->getCode());
          break;
        case 'isRequired':
          $e->setIsRequired((bool)$val);
          break;
        default:
          break;
      }
    }
  }
}
