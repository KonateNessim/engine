<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\OperatorType;
use App\Repository\PlaceRepository;


#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  private ?int $id = null;

  #[ORM\ManyToOne(targetEntity: MethodLine::class)]
  private MethodLine $line;

  #[ORM\ManyToOne(targetEntity: Argument::class)]
  private ?Argument $argument = null;

  #[ORM\Column(type: 'string', enumType: OperatorType::class, nullable: true)]
  private ?OperatorType $operator = null;

  #[ORM\Column(type: 'string', nullable: true)]
  private ?string $value = null;

  #[ORM\Column(type: 'integer')]
  private int $orderIndex = 1;

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getLine(): MethodLine
  {
    return $this->line;
  }
  public function setLine(MethodLine $l): void
  {
    $this->line = $l;
  }
  public function getArgument(): ?Argument
  {
    return $this->argument;
  }
  public function setArgument(?Argument $a): void
  {
    $this->argument = $a;
  }
  public function getOperator(): ?OperatorType
  {
    return $this->operator;
  }
  public function setOperator(?OperatorType $o): void
  {
    $this->operator = $o;
  }
  public function getValue(): ?string
  {
    return $this->value;
  }
  public function setValue(?string $v): void
  {
    $this->value = $v;
  }
  public function getOrderIndex(): int
  {
    return $this->orderIndex;
  }
  public function setOrderIndex(int $i): void
  {
    $this->orderIndex = $i;
  }
}
