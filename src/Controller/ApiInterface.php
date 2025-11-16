<?php

namespace App\Controller;

use App\Service\PaginationService;
use App\Service\SnapshotManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiInterface extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        protected EntityManagerInterface $em,
        private PaginationService $paginate,
        private SnapshotManager $snapshots
    ) {}

    private int $statusCode = 200;
    private string $message = "Opération effectuée avec succès";

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function responseData(
        $data = [],
        $group = null,
        bool $paginate = false
    ): JsonResponse {
        $context = $group ? [AbstractNormalizer::GROUPS => $group] : [];

        if ($paginate && $data ) {
            $data = $this->paginate->paginate($data);
            $items = $this->serializer->serialize($data->getItems(), 'json', $context);

            return new JsonResponse([
                'code' => 200,
                'message' => $this->message,
                'data' => json_decode($items, true),
                'pagination' => [
                    'currentPage'  => $data->getCurrentPageNumber(),
                    'totalItems'   => $data->getTotalItemCount(),
                    'itemsPerPage' => $data->getItemNumberPerPage(),
                    'totalPages'   => ceil($data->getTotalItemCount() / $data->getItemNumberPerPage())
                ],
                'errors' => []
            ]);
        }

        $json = $this->serializer->serialize($data, 'json', $context);

        return new JsonResponse([
            'code' => $this->statusCode,
            'message' => $this->message,
            'data' => json_decode($json, true),
            'errors' => []
        ]);
    }

    public function errorResponse($DTO, string $customMessage = ''): ?JsonResponse
    {
        $errors = $this->validator->validate($DTO);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse([
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ], 400);
        } elseif ($customMessage !== '') {
            return new JsonResponse([
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => [$customMessage]
            ], 400);
        }

        return null;
    }


    
}
