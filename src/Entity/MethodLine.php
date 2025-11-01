<?php

namespace App\Entity;

use App\Repository\MethodLineRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MethodLineRepository::class)]
class MethodLine
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  #[Groups(["method"])]
  private ?int $id = null;

  #[ORM\Column(type: 'integer')]
  #[Groups(["method"])]
  private int $orderIndex = 1;

  #[ORM\Column(type: 'text', nullable: true)]
  #[Groups(["method"])]
  private ?string $expression = null;

  #[ORM\Column(length: 100, nullable: true)]
  #[Groups(["method"])]
  private ?string $resultVariable = null;

  #[ORM\Column(length: 50, nullable: true)]
  #[Groups(["method"])]
  private ?string $lineType = 'calculation';

  #[ORM\Column(type: 'json', nullable: true)]
  #[Groups(["method"])]
  private ?array $metadata = null;

  #[ORM\Column(type: 'datetime_immutable')]
  #[Groups(["method"])]
  private \DateTimeImmutable $createdAt;

  #[ORM\ManyToOne(inversedBy: 'methodLines')]
  private ?Method $method = null;

  public function __construct()
  {
    $this->createdAt = new \DateTimeImmutable();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getOrderIndex(): int
  {
    return $this->orderIndex;
  }
  public function setOrderIndex(int $i): void
  {
    $this->orderIndex = $i;
  }
  public function getExpression(): ?string
  {
    return $this->expression;
  }
  public function setExpression(?string $e): void
  {
    $this->expression = $e;
  }
  public function getResultVariable(): ?string
  {
    return $this->resultVariable;
  }
  public function setResultVariable(?string $r): void
  {
    $this->resultVariable = $r;
  }
  public function getLineType(): ?string
  {
    return $this->lineType;
  }
  public function setLineType(?string $t): void
  {
    $this->lineType = $t;
  }
  public function getMetadata(): ?array
  {
    return $this->metadata;
  }
  public function setMetadata(?array $m): void
  {
    $this->metadata = $m;
  }
  public function getCreatedAt(): \DateTimeImmutable
  {
    return $this->createdAt;
  }
  public function setCreatedAt(\DateTimeImmutable $d): void
  {
    $this->createdAt = $d;
  }

  public function getMethod(): ?Method
  {
      return $this->method;
  }

  public function setMethod(?Method $method): static
  {
      $this->method = $method;

      return $this;
  }
}
