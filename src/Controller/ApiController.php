<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;

use App\Entity\Method;
use App\Service\CalculationService;
use App\DTO\CalcRequest;

#[Route('/api/admin')]
#[OA\Tag(name: 'general')]
class ApiController extends AbstractController
{
  public function __construct(
    private CalculationService $calc,
    private EntityManagerInterface $em
  ) {}

  // ✅ --- CALCUL PAR ID ---
  #[Route('/calculate', methods: ['POST'])]
  #[OA\Post(
    path: "/api/admin/calculate",
    summary: "Effectuer un calcul à partir de l’ID de la méthode",
    description: "Utilise la méthode et ses lignes (MethodLine) pour exécuter un calcul complet.",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "methodId", type: "integer", example: 1),
          new OA\Property(
            property: "inputs",
            type: "object",
            example: ["VAR1" => 100, "VAR2" => 200, "AGE" => 30]
          )
        ]
      )
    ),
    responses: [
      new OA\Response(response: 200, description: "Résultat du calcul"),
      new OA\Response(response: 400, description: "Erreur de calcul ou inputs invalides"),
    ]
  )]
  public function calculate(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];

    if (empty($d['methodId'])) {
      return $this->json(['status' => 'ERROR', 'message' => 'methodId requis'], 400);
    }

    $inputs = $this->normalizeInputs($d['inputs'] ?? []);
    $req = new CalcRequest((int) $d['methodId'], $inputs);

    try {
      $res = $this->calc->calculate($req);

      $outputs = $res->outputs;
      $logs = $outputs['messages'] ?? [];
      unset($outputs['messages']);

      return $this->json([
        'status'  => $res->status->value,
        'outputs' => $outputs,
        'logs'    => $logs,
        'timeMs'  => $res->timeMs,
      ]);
    } catch (\Throwable $e) {
      return $this->json(['status' => 'ERROR', 'message' => $e->getMessage()], 400);
    }
  }

  // ✅ --- CALCUL PAR CODE ---
  #[Route('/calculate-by-code', methods: ['POST'])]
  #[OA\Post(
    path: "/api/admin/calculate-by-code",
    summary: "Effectuer un calcul par code de méthode",
    description: "Recherche une méthode via son code, puis exécute les calculs associés.  
- Si `insurerId` est fourni, le moteur cherche la méthode spécifique à cet assureur.  
- Sinon, il prend la méthode globale (`insurer = null`).",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        type: "object",
        properties: [
          new OA\Property(property: "methodCode", type: "string", example: "CALC_PRIME_AUTO"),
          new OA\Property(property: "insurerId", type: "integer", nullable: true, example: 10),
          new OA\Property(
            property: "inputs",
            type: "object",
            example: ["VAR1" => 100, "VAR2" => 200, "VAR3" => 2, "AGE" => 30]
          )
        ]
      )
    )
  )]
  public function calculateByCode(Request $r): JsonResponse
  {
    $d = json_decode($r->getContent(), true) ?? [];

    if (empty($d['methodCode'])) {
      return $this->json(['status' => 'ERROR', 'message' => 'methodCode requis'], 400);
    }

    $repo = $this->em->getRepository(Method::class);
//dd(!empty($d['insurerId']));
    // 🔍 1. Recherche méthode spécifique à un assureur
    $method = !empty($d['insurerId'])
      ? $repo->findOneBy(['code' => $d['methodCode'], 'insurer' => (int) $d['insurerId']])
      : null;

    // 🔄 2. Sinon on prend la version globale
    if (!$method) {
      $method = $repo->findOneBy(['code' => $d['methodCode'], 'insurer' => null]);
    }

    if (!$method) {
      return $this->json(['status' => 'ERROR', 'message' => 'Méthode introuvable'], 404);
    }

    $inputs = $this->normalizeInputs($d['inputs'] ?? []);
    $req = new CalcRequest($method->getId(),  $inputs);

    try {
      $res = $this->calc->calculate($req);
      return $this->json([
        'status'  => $res->status->value,
        'outputs' => $res->outputs,
        'timeMs'  => $res->timeMs,
      ]);
    } catch (\Throwable $e) {
      return $this->json([
        'status'  => 'ERROR',
        'message' => $e->getMessage(),
      ], 400);
    }
  }

  // 🧩 --- Normalisation des entrées (dates → DateTimeImmutable) ---
  private function normalizeInputs(array $inputs): array
  {
    foreach ($inputs as $k => $v) {
      if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
        $inputs[$k] = new \DateTimeImmutable($v);
      }
    }
    return $inputs;
  }
}
