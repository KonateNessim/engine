<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\DTO\CalcRequest;
use App\Service\CalculationService;
use OpenApi\Attributes as OA;

#[Route('/api')]
class ApiController extends AbstractController
{
  public function __construct(private CalculationService $calc) {}

  #[Route('/calculate', methods: ['POST'])]
  #[OA\Post(
    path: "/api/calculate",
    summary: "Effectuer un calcul",
    description: "Prend en entrée un methodId, une version et des inputs, puis renvoie le résultat du moteur de calcul.",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "methodId", type: "integer", example: 1),
          new OA\Property(property: "versionNumber", type: "string", example: "v1"),
          new OA\Property(
            property: "inputs",
            type: "object",
            example: ["VAR1" => 100, "VAR2" => 200, "AGE" => 30]
          )
        ]
      )
    ),
    responses: [
      new OA\Response(
        response: 200,
        description: "Résultat du calcul",
        content: new OA\JsonContent(
          type: "object",
          properties: [
            new OA\Property(property: "status", type: "string", example: "SUCCESS"),
            new OA\Property(
              property: "outputs",
              type: "object",
              example: ["prime_base" => 1200.50, "bonus" => 50]
            ),
            new OA\Property(property: "timeMs", type: "integer", example: 15)
          ]
        )
      ),
      new OA\Response(response: 400, description: "Erreur de validation ou de calcul")
    ]
  )]
  public function calculate(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];
    if (!isset($d['methodId'])) {
      return $this->json(['status' => 'ERROR', 'message' => 'methodId requis'], 400);
    }

    $req = new CalcRequest((int)$d['methodId'], $d['versionNumber'] ?? 'v1', $d['inputs'] ?? []);

    try {
      $res = $this->calc->calculate($req);
      return $this->json([
        'status' => $res->status->value,
        'outputs' => $res->outputs,
        'timeMs' => $res->timeMs
      ]);
    } catch (\Throwable $e) {
      return $this->json(['status' => 'ERROR', 'message' => $e->getMessage()], 400);
    }
  }
}
