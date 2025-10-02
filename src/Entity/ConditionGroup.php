<?php

namespace App\Entity;

use App\Repository\ConditionGroupRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ConditionGroupRepository::class)]
class ConditionGroup
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  private ?int $id = null;

  #[ORM\ManyToOne(targetEntity: MethodLine::class)]
  private MethodLine $line;

  #[ORM\Column(length: 3)]
  private string $logicOperator = 'AND'; // AND | OR

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
  public function getLogicOperator(): string
  {
    return $this->logicOperator;
  }
  public function setLogicOperator(string $o): void
  {
    $this->logicOperator = strtoupper($o);
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
