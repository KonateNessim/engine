<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Argument;
use App\Repository\ArgumentRepository;
use OpenApi\Attributes as OA;

#[Route('/api/admin/argument')]
#[OA\Tag(name: 'Argument')]
class ArgumentController extends ApiInterface
{
  /*   public function __construct(private EntityManagerInterface $em) {} */

  #[Route('', methods: ['GET'])]
  #[OA\Get(
    summary: "Lister tous les Arguments",
    description: "Retourne la liste de tous les Arguments disponibles",
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des Arguments",
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
  public function list(ArgumentRepository $repository): JsonResponse
  {
    $all = $repository->findAll();

    return $this->responseData($all, 'group1');
  }

  #[Route('/{id}', methods: ['GET'])]
  #[OA\Get(
    summary: "Afficher un Argument",
    description: "Retourne les détails d’un Argument par son identifiant",
    parameters: [
      new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    responses: [
      new OA\Response(
        response: 200,
        description: "Argument trouvé",
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
    $e = $this->em->find(Argument::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    return $this->json(['id' => $e->getId()]);
  }

  #[Route('', methods: ['POST'])]
  #[OA\Post(
    path: "/admin/argument",
    summary: "Créer un argument",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "name", type: "string"),
          new OA\Property(property: "label", type: "string", nullable: true),
          new OA\Property(property: "type", type: "string", example: "float"),
          new OA\Property(property: "defaultValue", type: "string", nullable: true),
          new OA\Property(property: "isRequired", type: "boolean"),
          new OA\Property(property: "constraints", type: "string", nullable: true)
        ]
      )
    ),
    responses: [
      new OA\Response(response: 200, description: "Identifiant de l'argument créé")
    ]
  )]
  public function createArgument(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];
    $a = new Argument();
    $a->setName($d['name']);
    $a->setLabel($d['label'] ?? null);
    $a->setType($d['type'] ?? 'float');
    $a->setDefaultValue($d['defaultValue'] ?? null);
    $a->setIsRequired((bool)($d['isRequired'] ?? true));
    $a->setConstraints($d['constraints'] ?? null);
    $this->em->persist($a);
    $this->em->flush();
    return $this->json(['id' => $a->getId()]);
  }


  #[Route('/{id}', methods: ['PUT'])]
  #[OA\Put(
    path: "/admin/argument/{id}",
    summary: "Mettre à jour un argument",
    parameters: [
      new OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Identifiant de l'argument à mettre à jour",
        schema: new OA\Schema(type: "integer", example: 1)
      )
    ],
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "name", type: "string"),
          new OA\Property(property: "label", type: "string", nullable: true),
          new OA\Property(property: "type", type: "string", example: "float"),
          new OA\Property(property: "defaultValue", type: "string", nullable: true),
          new OA\Property(property: "isRequired", type: "boolean"),
          new OA\Property(property: "constraints", type: "string", nullable: true)
        ]
      )
    ),
    responses: [
      new OA\Response(
        response: 200,
        description: "Argument mis à jour avec succès",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "id", type: "integer", example: 1),
            new OA\Property(property: "updated", type: "boolean", example: true)
          ]
        )
      ),
      new OA\Response(response: 404, description: "Argument non trouvé")
    ]
  )]
  public function updateArgument(int $id, Request $r): JsonResponse
  {
    $a = $this->em->find(Argument::class, $id);
    if (!$a) {
      return $this->json(['error' => 'not found'], 404);
    }

    $d = json_decode($r->getContent(), true) ?? [];

    if (isset($d['name'])) $a->setName($d['name']);
    if (array_key_exists('label', $d)) $a->setLabel($d['label']);
    if (isset($d['type'])) $a->setType($d['type']);
    if (array_key_exists('defaultValue', $d)) $a->setDefaultValue($d['defaultValue']);
    if (isset($d['isRequired'])) $a->setIsRequired((bool)$d['isRequired']);
    if (array_key_exists('constraints', $d)) $a->setConstraints($d['constraints']);

    $this->em->flush();

    return $this->json(['id' => $a->getId(), 'updated' => true]);
  }


  #[Route('/{id}', methods: ['DELETE'])]
  #[OA\Delete(
    summary: "Supprimer un Argument",
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
    $e = $this->em->find(Argument::class, $id);
    if (!$e) return $this->json(['error' => 'not found'], 404);
    $this->em->remove($e);
    $this->em->flush();
    return $this->json(['deleted' => true]);
  }

  private function apply(Argument $e, array $d): void
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
