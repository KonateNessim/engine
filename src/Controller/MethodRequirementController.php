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

/**
 * Contrôleur pour la gestion des MethodRequirement
 * Gère les exigences et paramètres requis pour les méthodes de calcul
 */
#[Route('/api/engine/requirement')]
#[OA\Tag(name: 'MethodRequirement', description: 'Gestion des exigences de méthodes')]
class MethodRequirementController extends ApiInterface
{
    /**
     * Liste tous les MethodRequirement disponibles
     */
    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: "/api/engine/requirement",
        summary: "Lister tous les MethodRequirement",
        description: "Retourne la liste complète de toutes les exigences de méthodes dans le système",
        tags: ['MethodRequirement'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des MethodRequirement récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant unique du requirement"),
                            new OA\Property(property: "label", type: "string", example: "Durée du prêt", description: "Libellé du requirement"),
                            new OA\Property(property: "code", type: "string", example: "DUREE", description: "Code unique du requirement"),
                            new OA\Property(property: "isRequired", type: "boolean", example: true, description: "Indique si le champ est obligatoire"),
                            new OA\Property(
                                property: "validationRules", 
                                type: "object", 
                                example: ["min" => 18, "max" => 80],
                                description: "Règles de validation du champ"
                            ),
                            new OA\Property(property: "defaultValue", type: "string", nullable: true, example: "0", description: "Valeur par défaut"),
                            new OA\Property(property: "method", type: "integer", example: 1, description: "ID de la méthode associée")
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

    /**
     * Récupère les détails d'un MethodRequirement spécifique
     */
    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        path: "/api/engine/requirement/{id}",
        summary: "Détails d'un MethodRequirement",
        description: "Retourne les informations détaillées d'une exigence de méthode spécifique",
        tags: ['MethodRequirement'],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Identifiant unique du requirement",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "MethodRequirement trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1, description: "Identifiant du requirement"),
                        new OA\Property(property: "label", type: "string", example: "Durée du prêt", description: "Libellé du requirement"),
                        new OA\Property(property: "code", type: "string", example: "DUREE", description: "Code unique"),
                        new OA\Property(property: "isRequired", type: "boolean", example: true, description: "Champ obligatoire"),
                        new OA\Property(
                            property: "validationRules",
                            type: "object",
                            example: ["min" => 18, "max" => 80],
                            description: "Règles de validation"
                        ),
                        new OA\Property(property: "defaultValue", type: "string", nullable: true, example: "0", description: "Valeur par défaut"),
                        new OA\Property(property: "method", type: "integer", example: 1, description: "ID de la méthode")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "MethodRequirement non trouvé")
        ]
    )]
    public function detail(int $id): JsonResponse
    {
        $e = $this->em->find(MethodRequirement::class, $id);
        if (!$e) return $this->json(['error' => 'not found'], 404);
        return $this->responseData($e, 'group_requirements', true);
    }

    /**
     * Crée un nouveau MethodRequirement
     */
    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        path: "/api/engine/requirement/create",
        summary: "Créer un nouveau MethodRequirement",
        description: "Crée une nouvelle exigence de méthode avec ses règles de validation et paramètres",
        tags: ['MethodRequirement']
    )]
    #[OA\RequestBody(
        required: true,
        description: "Données du nouveau requirement à créer",
        content: new OA\JsonContent(
            type: "object",
            required: ["label", "code", "method"],
            properties: [
                new OA\Property(
                    property: "label",
                    type: "string",
                    example: "Durée du prêt",
                    description: "Libellé descriptif du requirement (obligatoire)"
                ),
                new OA\Property(
                    property: "code",
                    type: "integer",
                    example: 1,
                    description: "ID de l'argument associé (obligatoire) - utilisé pour récupérer le code"
                ),
                new OA\Property(
                    property: "isRequired",
                    type: "boolean",
                    example: false,
                    description: "Indique si ce champ est obligatoire (par défaut: false)"
                ),
                new OA\Property(
                    property: "validationRules",
                    type: "object",
                    example: ["min" => 18, "max" => 80],
                    description: "Règles de validation JSON (min, max, pattern, etc.)"
                ),
                new OA\Property(
                    property: "defaultValue",
                    type: "string",
                    nullable: true,
                    example: "0",
                    description: "Valeur par défaut du champ (optionnel)"
                ),
                new OA\Property(
                    property: "method",
                    type: "integer",
                    example: 1,
                    description: "ID de la méthode à laquelle associer ce requirement (obligatoire)"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "MethodRequirement créé avec succès",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "id", type: "integer", example: 5, description: "ID du requirement créé"),
                new OA\Property(property: "label", type: "string", example: "Durée du prêt", description: "Libellé"),
                new OA\Property(property: "code", type: "string", example: "DUREE", description: "Code"),
                new OA\Property(property: "isRequired", type: "boolean", example: false, description: "Est obligatoire"),
                new OA\Property(property: "method", type: "integer", example: 1, description: "ID de la méthode")
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Données invalides")]
    #[OA\Response(response: 404, description: "Méthode ou argument non trouvé")]
    public function create(Request $r): JsonResponse
    {
        $d = json_decode($r->getContent(), true) ?? [];
        $e = new MethodRequirement();
        $this->apply($e, $d);
        $this->em->persist($e);
        $this->em->flush();
        return $this->responseData($e, 'group_requirements', true);
    }

    /**
     * Met à jour un MethodRequirement existant
     */
    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: "/api/engine/requirement/{id}",
        summary: "Mettre à jour un MethodRequirement",
        description: "Met à jour les informations d'une exigence de méthode existante. Seuls les champs fournis seront mis à jour.",
        tags: ['MethodRequirement']
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Identifiant unique du requirement à mettre à jour",
        schema: new OA\Schema(type: "integer", example: 5)
    )]
    #[OA\RequestBody(
        required: true,
        description: "Données à mettre à jour (tous les champs sont optionnels)",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(
                    property: "method",
                    type: "integer",
                    example: 1,
                    description: "Nouvel ID de la méthode associée"
                ),
                new OA\Property(
                    property: "label",
                    type: "string",
                    example: "Durée du prêt (mise à jour)",
                    description: "Nouveau libellé du requirement"
                ),
                new OA\Property(
                    property: "code",
                    type: "integer",
                    example: 2,
                    description: "Nouvel ID de l'argument (pour récupérer le code)"
                ),
                new OA\Property(
                    property: "isRequired",
                    type: "boolean",
                    example: true,
                    description: "Nouveau statut obligatoire"
                ),
                new OA\Property(
                    property: "validationRules",
                    type: "object",
                    example: ["min" => 12, "max" => 120],
                    description: "Nouvelles règles de validation"
                ),
                new OA\Property(
                    property: "defaultValue",
                    type: "string",
                    nullable: true,
                    example: "12",
                    description: "Nouvelle valeur par défaut"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "MethodRequirement mis à jour avec succès",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "id", type: "integer", example: 5, description: "ID du requirement"),
                new OA\Property(property: "updated", type: "boolean", example: true, description: "Confirmation de la mise à jour"),
                new OA\Property(property: "label", type: "string", example: "Durée du prêt (mise à jour)", description: "Libellé mis à jour"),
                new OA\Property(property: "code", type: "string", example: "DUREE_V2", description: "Code mis à jour"),
                new OA\Property(property: "isRequired", type: "boolean", example: true, description: "Statut mis à jour")
            ]
        )
    )]
    #[OA\Response(response: 404, description: "MethodRequirement non trouvé")]
    #[OA\Response(response: 400, description: "Données invalides")]
    public function update(int $id, Request $r): JsonResponse
    {
        $e = $this->em->find(MethodRequirement::class, $id);
        if (!$e) return $this->json(['error' => 'not found'], 404);
        $d = json_decode($r->getContent(), true) ?? [];
        $this->apply($e, $d);
        $this->em->flush();
        return $this->responseData($e, 'group_requirements', true);
    }

    /**
     * Supprime un MethodRequirement
     */
    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/engine/requirement/{id}",
        summary: "Supprimer un MethodRequirement",
        description: "Supprime définitivement une exigence de méthode par son identifiant",
        tags: ['MethodRequirement']
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Identifiant unique du requirement à supprimer",
        schema: new OA\Schema(type: "integer", example: 5)
    )]
    #[OA\Response(
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
    )]
    #[OA\Response(response: 404, description: "MethodRequirement non trouvé")]
    public function delete(int $id): JsonResponse
    {
        $e = $this->em->find(MethodRequirement::class, $id);
        if (!$e) return $this->json(['error' => 'not found'], 404);
        $this->em->remove($e);
        $this->em->flush();
        return $this->json(['deleted' => true]);
    }

    /**
     * Applique les modifications aux champs du MethodRequirement
     * 
     * @param MethodRequirement $e L'entité à modifier
     * @param array $d Les données à appliquer
     */
    private function apply(MethodRequirement $e, array $d): void
    {
        foreach ($d as $field => $val) {
            switch ($field) {
                case 'method':
                    $method = $this->em->find(Method::class, (int)$val);
                    if ($method) {
                        $e->setMethod($method);
                    }
                    break;
                case 'label':
                    $e->setLabel($val);
                    break;
                case 'code':
                    $argument = $this->em->find(Argument::class, (int)$val);
                    if ($argument) {
                        $e->setCode($argument->getCode());
                    }
                    break;
                case 'isRequired':
                    $e->setIsRequired((bool)$val);
                    break;
                case 'validationRules':
                    if (method_exists($e, 'setValidationRules')) {
                        $e->setValidationRules($val);
                    }
                    break;
                case 'defaultValue':
                    if (method_exists($e, 'setDefaultValue')) {
                        $e->setDefaultValue($val);
                    }
                    break;
                default:
                    break;
            }
        }
    }
}