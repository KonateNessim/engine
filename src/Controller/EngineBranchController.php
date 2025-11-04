<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EngineBranch;
use App\Enum\EngineStatus;
use App\Repository\EngineBranchRepository;
use OpenApi\Attributes as OA;

/**
 * Contrôleur pour la gestion des EngineBranch
 * Permet de créer, lire, mettre à jour et supprimer des moteurs associés aux branches
 */
#[Route('/api/engine/engineBranch')]
#[OA\Tag(name: 'EngineBranch', description: 'Gestion des moteurs par branche')]
class EngineBranchController extends ApiInterface
{
    /**
     * Liste tous les EngineBranch disponibles
     */
    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: "/api/engine/engineBranch",
        summary: "Lister tous les moteurs",
        description: "Retourne la liste complète de tous les EngineBranch disponibles dans le système",
        tags: ['EngineBranch'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des EngineBranch récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique du moteur"),
                            new OA\Property(property: "name", type: "string", example: "Main Engine", description: "Nom du moteur"),
                            new OA\Property(property: "description", type: "string", nullable: true, example: "Moteur principal", description: "Description détaillée du moteur"),
                            new OA\Property(property: "branch", type: "string", example: "uIII", description: "UID de la branche associée"),
                            new OA\Property(property: "status", type: "string", enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"], example: "ACTIVE", description: "Statut actuel du moteur")
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

    /**
     * Récupère les détails d'un EngineBranch spécifique par son ID
     */
    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Get(
        path: "/api/engine/engineBranch/get/one/{id}",
        summary: "Détails d'un engine branch",
        description: "Retourne les informations détaillées d'un moteur spécifique par son identifiant",
        tags: ['EngineBranch'],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                description: "Identifiant unique du moteur",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Moteur trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique du moteur"),
                        new OA\Property(property: "name", type: "string", example: "Main Engine", description: "Nom du moteur"),
                        new OA\Property(property: "description", type: "string", nullable: true, example: "Moteur principal", description: "Description du moteur"),
                        new OA\Property(property: "branch", type: "string", example: "uIII", description: "UID de la branche"),
                        new OA\Property(property: "status", type: "string", enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"], example: "ACTIVE", description: "Statut du moteur")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Moteur non trouvé")
        ]
    )]
    public function getOneMethod(EngineBranchRepository $engineBranchRepository, int $id): JsonResponse
    {
        $engineBranch = $engineBranchRepository->find($id);
        return $this->responseData($engineBranch, 'method', true);
    }

    /**
     * Liste tous les EngineBranch d'une branche spécifique
     */
    #[Route('/{uidBranch}', methods: ['GET'])]
    #[OA\Get(
        path: "/api/engine/engineBranch/{uidBranch}",
        summary: "Récupérer les engines branch d'une branche",
        description: "Retourne tous les EngineBranch associés à une branche spécifique identifiée par son UID",
        tags: ['EngineBranch'],
        parameters: [
            new OA\Parameter(
                name: "uidBranch", 
                in: "path", 
                required: true, 
                description: "UID de la branche",
                schema: new OA\Schema(type: "string", example: "uIII")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des moteurs de la branche récupérée",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant du moteur"),
                            new OA\Property(property: "name", type: "string", example: "Main Engine", description: "Nom du moteur"),
                            new OA\Property(property: "description", type: "string", nullable: true, example: "Moteur principal", description: "Description du moteur"),
                            new OA\Property(property: "branch", type: "string", example: "uIII", description: "UID de la branche"),
                            new OA\Property(property: "status", type: "string", enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"], example: "ACTIVE", description: "Statut du moteur")
                        ]
                    )
                )
            ),
            new OA\Response(response: 404, description: "Branche non trouvée ou aucun moteur associé")
        ]
    )]
    public function listByBranch(string $uidBranch, EngineBranchRepository $engineBranchRepository): JsonResponse
    {
        $e = $engineBranchRepository->findBy(['branch' => $uidBranch]);
        return $this->responseData($e, 'method', true);
    }

    /**
     * Crée un nouveau EngineBranch pour une branche donnée
     */
    #[Route('/create/{branch}', methods: ['POST'])]
    #[OA\Post(
        path: "/api/engine/engineBranch/create/{branch}",
        summary: "Créer un nouveau moteur",
        description: "Crée un nouvel EngineBranch associé à une branche spécifique",
        tags: ['EngineBranch'],
        parameters: [
            new OA\Parameter(
                name: "branch", 
                in: "path", 
                required: true, 
                description: "UID de la branche à laquelle associer le moteur",
                schema: new OA\Schema(type: "string", example: "uIII")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du nouveau moteur à créer",
            content: new OA\JsonContent(
                type: "object",
                required: ["name"],
                properties: [
                    new OA\Property(
                        property: "name", 
                        type: "string", 
                        example: "Main Engine",
                        description: "Nom du moteur (obligatoire)"
                    ),
                    new OA\Property(
                        property: "description", 
                        type: "string", 
                        nullable: true, 
                        example: "Moteur principal pour la production",
                        description: "Description détaillée du moteur (optionnel)"
                    ),
                    new OA\Property(
                        property: "status",
                        type: "string",
                        enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"],
                        example: "DRAFT",
                        description: "Statut initial du moteur (par défaut: DRAFT)"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Moteur créé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1, description: "ID du moteur créé"),
                        new OA\Property(property: "name", type: "string", example: "Main Engine", description: "Nom du moteur"),
                        new OA\Property(property: "description", type: "string", nullable: true, example: "Moteur principal", description: "Description du moteur"),
                        new OA\Property(property: "branch", type: "string", example: "uIII", description: "UID de la branche"),
                        new OA\Property(property: "status", type: "string", example: "DRAFT", description: "Statut du moteur")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Données invalides")
        ]
    )]
    public function createEngine(Request $r, $branch): JsonResponse
    {
        $d = json_decode($r->getContent(), true) ?? [];
        $e = new EngineBranch();
        $e->setName($d['name'] ?? 'Engine');
        $e->setDescription($d['description'] ?? null);
        $e->setBranch($branch);
        $e->setStatus(EngineStatus::from($d['status'] ?? 'DRAFT'));
        $this->em->persist($e);
        $this->em->flush();
        return $this->responseData($e, 'method', true);
    }

    /**
     * Met à jour un EngineBranch existant
     */
    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: "/api/engine/engineBranch/{id}",
        summary: "Mettre à jour un moteur",
        description: "Met à jour les informations d'un EngineBranch existant. Seuls les champs fournis seront mis à jour.",
        tags: ['EngineBranch'],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                description: "Identifiant unique du moteur à mettre à jour",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données à mettre à jour (tous les champs sont optionnels)",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "name", 
                        type: "string",
                        example: "Updated Engine Name",
                        description: "Nouveau nom du moteur"
                    ),
                    new OA\Property(
                        property: "description", 
                        type: "string", 
                        nullable: true,
                        example: "Description mise à jour",
                        description: "Nouvelle description du moteur"
                    ),
                    new OA\Property(
                        property: "branch", 
                        type: "string",
                        example: "uXYZ",
                        description: "Nouvel UID de la branche associée"
                    ),
                    new OA\Property(
                        property: "status",
                        type: "string",
                        enum: ["DRAFT", "ACTIVE", "DEPRECATED", "ARCHIVED"],
                        example: "ACTIVE",
                        description: "Nouveau statut du moteur"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Moteur mis à jour avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1, description: "ID du moteur"),
                        new OA\Property(property: "name", type: "string", example: "Updated Engine", description: "Nom mis à jour"),
                        new OA\Property(property: "description", type: "string", nullable: true, example: "Description mise à jour", description: "Description mise à jour"),
                        new OA\Property(property: "branch", type: "string", example: "uIII", description: "UID de la branche"),
                        new OA\Property(property: "status", type: "string", example: "ACTIVE", description: "Statut mis à jour")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Moteur non trouvé"),
            new OA\Response(response: 400, description: "Données invalides")
        ]
    )]
    public function update(int $id, Request $r): JsonResponse
    {
        $e = $this->em->find(EngineBranch::class, $id);
        if (!$e) return $this->responseData(['error' => 'not found'], 404);
        $d = json_decode($r->getContent(), true) ?? [];
        $this->apply($e, $d);
        $this->em->flush();
        return $this->responseData($e, 'method', true);
    }

    /**
     * Supprime un EngineBranch
     */
    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/engine/engineBranch/{id}",
        summary: "Supprimer un moteur",
        description: "Supprime définitivement un EngineBranch par son identifiant",
        tags: ['EngineBranch'],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                description: "Identifiant unique du moteur à supprimer",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Suppression réussie",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "deleted", 
                            type: "boolean", 
                            example: true,
                            description: "Confirmation de la suppression"
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Moteur non trouvé")
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

    /**
     * Applique les modifications aux champs de l'EngineBranch
     * 
     * @param EngineBranch $e L'entité à modifier
     * @param array $d Les données à appliquer
     */
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
                    $e->setBranch($val);
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