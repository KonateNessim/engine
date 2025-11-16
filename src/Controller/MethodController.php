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

/**
 * Contrôleur pour la gestion des Methods
 * Permet de créer, lire et mettre à jour des méthodes associées aux moteurs et assureurs
 */
#[Route('/api/engine/method')]
#[OA\Tag(name: 'Method', description: 'Gestion des méthodes de calcul')]
class MethodController extends ApiInterface
{
  /**
   * Liste toutes les méthodes globales (non associées à une branche ou assureur)
   */
  #[Route('/global', methods: ['GET'])]
  #[OA\Get(
    path: "/api/engine/method/global",
    summary: "Lister les méthodes globales",
    description: "Retourne la liste de toutes les méthodes globales qui ne sont associées ni à une branche ni à un assureur spécifique",
    tags: ['Method'],
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des méthodes globales récupérée avec succès",
        content: new OA\JsonContent(
          type: "array",
          items: new OA\Items(
            type: "object",
            properties: [
              new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique de la méthode"),
              new OA\Property(property: "name", type: "string", example: "Calcul Standard", description: "Nom de la méthode"),
              new OA\Property(property: "code", type: "string", example: "CALC_STANDARD", description: "Code unique de la méthode"),
              new OA\Property(property: "publicName", type: "string", nullable: true, example: "Calcul Standard", description: "Nom public de la méthode"),
              new OA\Property(property: "description", type: "string", nullable: true, example: "Méthode de calcul standard", description: "Description détaillée"),
              new OA\Property(property: "engineBranch", type: "integer", nullable: true, example: null, description: "ID de la branche (null pour global)"),
              new OA\Property(property: "insurer", type: "string", nullable: true, example: null, description: "Code de l'assureur (null pour global)")
            ]
          )
        )
      )
    ]
  )]
  public function listGlobal(MethodRepository $methodRepository): JsonResponse
  {

   /*  dd(""); */
    $all = $methodRepository->findBy([
      /* 'engine' => null,
      'insurer' => null */
    ]);
    return $this->responseData($all, 'method', true);
  }

  /**
   * Crée une nouvelle méthode
   */
  #[Route('/create', methods: ['POST'])]
  #[OA\Post(
    path: "/api/engine/method/create",
    summary: "Créer une nouvelle méthode",
    description: "Crée une nouvelle méthode de calcul associée optionnellement à une branche et/ou un assureur",
    tags: ['Method']
  )]
  #[OA\RequestBody(
    required: true,
    description: "Données de la nouvelle méthode à créer",
    content: new OA\JsonContent(
      type: "object",
      required: ["name", "code"],
      properties: [
        new OA\Property(
          property: "name",
          type: "string",
          example: "Calcul Prime Auto",
          description: "Nom de la méthode (obligatoire)"
        ),
        new OA\Property(
          property: "code",
          type: "string",
          example: "CALC_PRIME_AUTO",
          description: "Code unique de la méthode (obligatoire)"
        ),
        new OA\Property(
          property: "engineBranch",
          type: "integer",
          nullable: true,
          example: 1,
          description: "ID de la branche à associer (optionnel)"
        ),
        new OA\Property(
          property: "insurer",
          type: "string",
          nullable: true,
          example: "INS001",
          description: "Code de l'assureur à associer (optionnel)"
        ),
        new OA\Property(
          property: "publicName",
          type: "string",
          nullable: true,
          example: "Prime Automobile",
          description: "Nom public de la méthode (optionnel)"
        ),
        new OA\Property(
          property: "description",
          type: "string",
          nullable: true,
          example: "Méthode pour le calcul de prime automobile",
          description: "Description détaillée de la méthode (optionnel)"
        )
      ]
    )
  )]
  #[OA\Response(
    response: 201,
    description: "Méthode créée avec succès",
    content: new OA\JsonContent(
      type: "object",
      properties: [
        new OA\Property(property: "id", type: "integer", example: 1, description: "ID de la méthode créée"),
        new OA\Property(property: "name", type: "string", example: "Calcul Prime Auto", description: "Nom de la méthode"),
        new OA\Property(property: "code", type: "string", example: "CALC_PRIME_AUTO", description: "Code de la méthode"),
        new OA\Property(property: "publicName", type: "string", nullable: true, example: "Prime Automobile", description: "Nom public"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Description", description: "Description"),
        new OA\Property(property: "engineBranch", type: "integer", nullable: true, example: 1, description: "ID de la branche"),
        new OA\Property(property: "insurer", type: "string", nullable: true, example: "INS001", description: "Code assureur")
      ]
    )
  )]
  #[OA\Response(response: 400, description: "Données invalides ou code déjà existant")]
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

  /**
   * Récupère les détails d'une méthode spécifique
   */
  #[Route('/get/one/{id}', methods: ['GET'])]
  #[OA\Get(
    path: "/api/engine/method/get/one/{id}",
    summary: "Détails d'une méthode",
    description: "Retourne les informations détaillées d'une méthode spécifique par son identifiant",
    tags: ['Method'],
    parameters: [
      new OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Identifiant unique de la méthode",
        schema: new OA\Schema(type: "integer", example: 1)
      )
    ],
    responses: [
      new OA\Response(
        response: 200,
        description: "Détails de la méthode récupérés avec succès",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique de la méthode"),
            new OA\Property(property: "name", type: "string", example: "Calcul Prime Auto", description: "Nom de la méthode"),
            new OA\Property(property: "code", type: "string", example: "CALC_PRIME_AUTO", description: "Code unique de la méthode"),
            new OA\Property(property: "publicName", type: "string", nullable: true, example: "Prime Automobile", description: "Nom public de la méthode"),
            new OA\Property(property: "description", type: "string", nullable: true, example: "Méthode pour le calcul de prime auto", description: "Description détaillée"),
            new OA\Property(property: "engineBranch", type: "integer", nullable: true, example: 1, description: "ID de la branche associée"),
            new OA\Property(property: "insurer", type: "string", nullable: true, example: "INS001", description: "Code de l'assureur associé")
          ]
        )
      ),
      new OA\Response(response: 404, description: "Méthode non trouvée")
    ]
  )]
  public function getOneMethod(MethodRepository $methodRepository, int $id): JsonResponse
  {
    $method = $methodRepository->find($id);
    return $this->responseData($method, 'method');
  }

  /**
   * Met à jour une méthode existante
   */
  #[Route('/update/{id}', methods: ['PUT'])]
  #[OA\Put(
    path: "/api/engine/method/update/{id}",
    summary: "Mettre à jour une méthode",
    description: "Met à jour les informations d'une méthode existante. Tous les champs fournis seront mis à jour.",
    tags: ['Method']
  )]
  #[OA\Parameter(
    name: "id",
    in: "path",
    required: true,
    description: "Identifiant unique de la méthode à mettre à jour",
    schema: new OA\Schema(type: "integer", example: 1)
  )]
  #[OA\RequestBody(
    required: true,
    description: "Données à mettre à jour",
    content: new OA\JsonContent(
      type: "object",
      required: ["name", "code"],
      properties: [
        new OA\Property(
          property: "name",
          type: "string",
          example: "Calcul Prime Auto Mise à Jour",
          description: "Nouveau nom de la méthode (obligatoire)"
        ),
        new OA\Property(
          property: "code",
          type: "string",
          example: "CALC_PRIME_AUTO_V2",
          description: "Nouveau code de la méthode (obligatoire)"
        ),
        new OA\Property(
          property: "engineBranch",
          type: "integer",
          nullable: true,
          example: 2,
          description: "Nouvel ID de la branche à associer (optionnel)"
        ),
        new OA\Property(
          property: "insurer",
          type: "string",
          nullable: true,
          example: "INS002",
          description: "Nouveau code de l'assureur à associer (optionnel)"
        ),
        new OA\Property(
          property: "publicName",
          type: "string",
          nullable: true,
          example: "Prime Auto V2",
          description: "Nouveau nom public (optionnel)"
        ),
        new OA\Property(
          property: "description",
          type: "string",
          nullable: true,
          example: "Version mise à jour de la méthode",
          description: "Nouvelle description (optionnel)"
        )
      ]
    )
  )]
  #[OA\Response(
    response: 200,
    description: "Méthode mise à jour avec succès",
    content: new OA\JsonContent(
      type: "object",
      properties: [
        new OA\Property(property: "id", type: "integer", example: 1, description: "ID de la méthode"),
        new OA\Property(property: "name", type: "string", example: "Calcul Prime Auto Mise à Jour", description: "Nom mis à jour"),
        new OA\Property(property: "code", type: "string", example: "CALC_PRIME_AUTO_V2", description: "Code mis à jour"),
        new OA\Property(property: "publicName", type: "string", nullable: true, example: "Prime Auto V2", description: "Nom public mis à jour"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Description mise à jour", description: "Description mise à jour"),
        new OA\Property(property: "engineBranch", type: "integer", nullable: true, example: 2, description: "ID de la branche"),
        new OA\Property(property: "insurer", type: "string", nullable: true, example: "INS002", description: "Code assureur")
      ]
    )
  )]
  #[OA\Response(response: 404, description: "Méthode non trouvée")]
  #[OA\Response(response: 400, description: "Données invalides")]
  public function updateMethod(Request $r, MethodRepository $methodRepository, int $id): JsonResponse
  {
    $method = $methodRepository->find($id);

    if (!$method) {
      return $this->responseData(['error' => 'Method not found'], 'error', false, 404);
    }

    $d = json_decode($r->getContent(), true) ?? [];

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

  /**
   * Liste les méthodes filtrées par branche et assureur
   */
  #[Route('/{engineBranch}/{insurer}', methods: ['GET'])]
  #[OA\Get(
    path: "/api/engine/method/{engineBranch}/{insurer}",
    summary: "Lister les méthodes par branche et assureur",
    description: "Retourne la liste des méthodes associées à une branche spécifique et un assureur spécifique",
    tags: ['Method'],
    parameters: [
      new OA\Parameter(
        name: "engineBranch",
        in: "path",
        required: true,
        description: "Identifiant ou code de la branche",
        schema: new OA\Schema(type: "string", example: "uIII")
      ),
      new OA\Parameter(
        name: "insurer",
        in: "path",
        required: true,
        description: "Code de l'assureur",
        schema: new OA\Schema(type: "string", example: "INS001")
      )
    ],
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des méthodes filtrées récupérée avec succès",
        content: new OA\JsonContent(
          type: "array",
          items: new OA\Items(
            type: "object",
            properties: [
              new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique de la méthode"),
              new OA\Property(property: "name", type: "string", example: "Calcul Prime Assureur", description: "Nom de la méthode"),
              new OA\Property(property: "code", type: "string", example: "CALC_PRIME_INS", description: "Code unique de la méthode"),
              new OA\Property(property: "publicName", type: "string", nullable: true, example: "Prime Assureur", description: "Nom public de la méthode"),
              new OA\Property(property: "description", type: "string", nullable: true, example: "Méthode spécifique à l'assureur", description: "Description détaillée"),
              new OA\Property(property: "engineBranch", type: "string", example: "uIII", description: "Code de la branche associée"),
              new OA\Property(property: "insurer", type: "string", example: "INS001", description: "Code de l'assureur associé")
            ]
          )
        )
      ),
      new OA\Response(response: 404, description: "Branche ou assureur non trouvé")
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

  /**
   * Liste toutes les méthodes d'une branche spécifique
   */
  #[Route('/{branch}', methods: ['GET'])]
  #[OA\Get(
    path: "/api/engine/method/{branch}",
    summary: "Lister les méthodes d'un engine branche",
    description: "Retourne la liste de toutes les méthodes associées à une engine branche spécifique",
    tags: ['Method'],
    parameters: [
      new OA\Parameter(
        name: "branch",
        in: "path",
        required: true,
        description: "Identifiant de la branche (engine)",
        schema: new OA\Schema(type: "string", example: 1)
      )
    ],
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des méthodes de la branche récupérée avec succès",
        content: new OA\JsonContent(
          type: "array",
          items: new OA\Items(
            type: "object",
            properties: [
              new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique de la méthode"),
              new OA\Property(property: "name", type: "string", example: "Calcul Prime Auto", description: "Nom de la méthode"),
              new OA\Property(property: "code", type: "string", example: "CALC_PRIME_AUTO", description: "Code unique de la méthode"),
              new OA\Property(property: "publicName", type: "string", nullable: true, example: "Prime Automobile", description: "Nom public de la méthode"),
              new OA\Property(property: "description", type: "string", nullable: true, example: "Méthode pour le calcul de prime auto", description: "Description détaillée"),
              new OA\Property(property: "engineBranch", type: "integer", example: 1, description: "ID de la branche associée"),
              new OA\Property(property: "insurer", type: "string", nullable: true, example: "INS001", description: "Code de l'assureur associé")
            ]
          )
        )
      ),
      new OA\Response(response: 404, description: "Branche non trouvée")
    ]
  )]
  public function list(MethodRepository $methodRepository, string $branch): JsonResponse
  {
    $all = $methodRepository->findBy([
      'engine' => $branch
    ]);
    return $this->responseData($all, 'method', true);
  }
}
