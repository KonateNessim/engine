<?php

namespace App\Controller;

use App\Entity\EngineBranch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Method;
use OpenApi\Attributes as OA;

#[Route('/api/engine/method')]
#[OA\Tag(name: 'Method')]
class MethodController extends ApiInterface
{

  #[Route('', methods: ['GET'])]
  #[OA\Get(
    path: "/admin/method",
    summary: "Lister les méthodes",
    description: "Retourne la liste des Method disponibles",
    responses: [
      new OA\Response(
        response: 200,
        description: "Liste des Method",
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
    $all = $this->em->getRepository(Method::class)->findAll();
    return $this->responseData($all, 'method', true);
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
                new OA\Property(property: "engineId", type: "integer", example: 1),
                new OA\Property(property: "insurerId", type: "integer", nullable: true, example: 10),
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

        if (isset($d['engineId'])) {
            $engine = $this->em->find(EngineBranch::class, $d['engineId']);
            $method->setEngine($engine);
        }

        if (isset($d['insurerId'])) {
            $method->setInsurer((int)$d['insurerId']);
        }

        $this->em->persist($method);
        $this->em->flush();

        return $this->responseData($method, 'method');
    }

    

}