<?php

namespace App\Controller;

use App\Entity\EngineBranch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Method;
use App\Repository\MethodRepository;
use OpenApi\Attributes as OA;

#[Route('/api/engine/method')]
#[OA\Tag(name: 'Method')]
class MethodController extends ApiInterface
{

  #[Route('/{branch}', methods: ['GET'])]
  #[OA\Get(
    path: "/branch/{branch}",
    summary: "Lister les méthodes",
    description: "Retourne la liste des Method par branch",
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des Method par branch",
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
  public function list(MethodRepository $methodRepository, int $branch): JsonResponse
  {
    $all = $methodRepository->findBy([
      'engine' => $branch
    ]);
    return $this->responseData($all, 'method', true);
  }

  #[Route('/global', methods: ['GET'])]
  #[OA\Get(
    path: "/method/global",
    summary: "Lister les méthodes globales",
    description: "Retourne la liste des Method globales",
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des Method globales",
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
  public function listGlobal(MethodRepository $methodRepository): JsonResponse
  {
    $all = $methodRepository->findBy([
      'engine' => null,
      'insurer' => null
    ]);
    return $this->responseData($all, 'method', true);
  }

  #[Route('/{engineBranch}/{insurer}', methods: ['GET'])]
  #[OA\Get(
    path: "/admin/method/global",
    summary: "Lister les méthodes globales",
    description: "Retourne la liste des Method par branch et insurer",
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des Method par branch et insurer",
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
  public function listByBranchAndInsurer(MethodRepository $methodRepository, string $engineBranch, string $insurer): JsonResponse
  {
    $all = $methodRepository->findBy([
      'engine' => $engineBranch,
      'insurer' => $insurer
    ]);
    return $this->responseData($all, 'method', true);
  }

  #[Route('/get/one/{id}', methods: ['GET'])]
  #[OA\Get(
    path: "/get/one/{id}",
    summary: "Détails d’une méthode",
    description: "Détails d’une méthode",
    responses: [
      new OA\Response(
        response: 200,
        description: "Détails d’une méthode",
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
  public function getOneMethod(MethodRepository $methodRepository, int $id): JsonResponse
  {
    $method = $methodRepository->find($id);
    return $this->responseData($method, 'method');
  }

  #[Route('/create', methods: ['POST'])]
  #[OA\Post(summary: "Créer une méthode associée à un moteur")]
  #[OA\RequestBody(
    required: true,
    content: new OA\JsonContent(
      type: "object",
      properties: [
        new OA\Property(property: "name", type: "string", example: "Calcul Prime Auto"),
        new OA\Property(property: "code", type: "string", example: "CALC_PRIME_AUTO"),
        new OA\Property(property: "engineBranch", type: "string", example: 1),
        new OA\Property(property: "insurer", type: "string", nullable: true, example: 10),
        new OA\Property(property: "publicName", type: "integer", nullable: true, example: 10),
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
    $method->setPublicName($d['publicName'] ?? null);
    $method->setDescription($d['description'] ?? null);

    if (isset($d['engineBranch'])) {
      $engine = $this->em->find(EngineBranch::class, $d['engineBranch']);
      $method->setEngine($engine);
    }

    if (isset($d['insurer'])) {
      $method->setInsurer($d['insurer']);
    }

    $this->em->persist($method);
    $this->em->flush();

    return $this->responseData($method, 'method');
  }

  #[Route('/{id}', methods: ['PUT'])]
  #[OA\Put(summary: "Mettre à jour une méthode")]
  #[OA\RequestBody(
    required: true,
    content: new OA\JsonContent(
      type: "object",
      properties: [
        new OA\Property(property: "name", type: "string", example: "Calcul Prime Auto"),
        new OA\Property(property: "code", type: "string", example: "CALC_PRIME_AUTO"),
        new OA\Property(property: "engineBranch", type: "string", example: 1),
        new OA\Property(property: "insurer", type: "string", nullable: true, example: 10),
        new OA\Property(property: "publicName", type: "integer", nullable: true, example: 10),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Méthode pour le calcul de prime auto")
      ]
    )
  )]
  #[OA\Response(response: 200, description: "Méthode mise à jour avec succès")]
  public function updateMethod(Request $r, MethodRepository $methodRepository, int $id): JsonResponse
  {
    $method = $methodRepository->find($id);
    $d = json_decode($r->getContent(), true) ?? [];
    $method->setName($d['name']);
    if (isset($d['engineBranch'])) {
      $engine = $this->em->find(EngineBranch::class, $d['engineBranch']);
      $method->setEngine($engine);
    }
    if (isset($d['insurer'])) {
      $method->setInsurer($d['insurer']);
    }
    $method->setCode($d['code']);
    $method->setPublicName($d['publicName'] ?? null);
    $method->setDescription($d['description'] ?? null);
    $this->em->persist($method);
    $this->em->flush();
    return $this->responseData($method, 'method');
  }
}
