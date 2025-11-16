<?php

namespace App\Entity;

use App\Repository\ConditionGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ConditionGroupRepository::class)]
class ConditionGroup
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  #[Groups(['methodLine'])]
  private ?int $id = null;



  #[ORM\Column(length: 3)]
  #[Groups(['methodLine'])]
  private string $logicOperator = 'AND'; // AND | OR

  #[ORM\Column(type: 'integer')]
  #[Groups(['methodLine'])]
  private int $orderIndex = 1;

  #[ORM\ManyToOne(inversedBy: 'conditionGroups')]
  private ?MethodLine $line = null;

  /**
   * @var Collection<int, Condition>
   */
  #[ORM\OneToMany(targetEntity: Condition::class, mappedBy: 'groupCondition')]
  #[Groups(['methodLine'])]
  private Collection $conditions;

  public function __construct()
  {
      $this->conditions = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
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

  public function getLine(): ?MethodLine
  {
      return $this->line;
  }

  public function setLine(?MethodLine $line): static
  {
      $this->line = $line;

      return $this;
  }

  /**
   * @return Collection<int, Condition>
   */
  public function getConditions(): Collection
  {
      return $this->conditions;
  }

  public function addCondition(Condition $condition): static
  {
      if (!$this->conditions->contains($condition)) {
          $this->conditions->add($condition);
          $condition->setGroupCondition($this);
      }

      return $this;
  }

  public function removeCondition(Condition $condition): static
  {
      if ($this->conditions->removeElement($condition)) {
          // set the owning side to null (unless already changed)
          if ($condition->getGroupCondition() === $this) {
              $condition->setGroupCondition(null);
          }
      }

      return $this;
  }
}
