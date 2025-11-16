<?php

namespace App\Entity;

use App\Repository\MethodLineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MethodLineRepository::class)]
class MethodLine
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  #[Groups(["method",'methodLine'])]
  private ?int $id = null;

  #[ORM\Column(type: 'integer')]
  #[Groups(["method",'methodLine'])]
  private int $orderIndex = 1;

  #[ORM\Column(type: 'text', nullable: true)]
  #[Groups(["method",'methodLine'])]
  private ?string $expression = null;

  #[ORM\Column(length: 100, nullable: true)]
  #[Groups(["method",'methodLine'])]
  private ?string $resultVariable = null;

  #[ORM\Column(length: 50, nullable: true)]
  #[Groups(["method",'methodLine'])]
  private ?string $lineType = 'calculation';

  #[ORM\Column(type: 'json', nullable: true)]
  #[Groups(["method",'methodLine'])]
  private ?array $metadata = null;

  #[ORM\Column(type: 'datetime_immutable')]
  #[Groups(["method"])]
  private \DateTimeImmutable $createdAt;

  #[ORM\ManyToOne(inversedBy: 'methodLines')]
  private ?Method $method = null;

  /**
   * @var Collection<int, Place>
   */
  #[ORM\OneToMany(targetEntity: Place::class, mappedBy: 'line')]
  #[Groups(['methodLine'])]
  private Collection $places;

  /**
   * @var Collection<int, ConditionGroup>
   */
  #[ORM\OneToMany(targetEntity: ConditionGroup::class, mappedBy: 'line')]
  #[Groups(['methodLine'])]
  private Collection $conditionGroups;

  /**
   * @var Collection<int, MethodLineVersion>
   */
  #[ORM\OneToMany(targetEntity: MethodLineVersion::class, mappedBy: 'line')]
  #[Groups(['methodLine'])]
  private Collection $methodLineVersions;



  public function __construct()
  {
    $this->createdAt = new \DateTimeImmutable();
    $this->places = new ArrayCollection();
    $this->conditionGroups = new ArrayCollection();
    $this->methodLineVersions = new ArrayCollection();
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

  /**
   * @return Collection<int, Place>
   */
  public function getPlaces(): Collection
  {
      return $this->places;
  }

  public function addPlace(Place $place): static
  {
      if (!$this->places->contains($place)) {
          $this->places->add($place);
          $place->setLine($this);
      }

      return $this;
  }

  public function removePlace(Place $place): static
  {
      if ($this->places->removeElement($place)) {
          // set the owning side to null (unless already changed)
          if ($place->getLine() === $this) {
              $place->setLine(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection<int, ConditionGroup>
   */
  public function getConditionGroups(): Collection
  {
      return $this->conditionGroups;
  }

  public function addConditionGroup(ConditionGroup $conditionGroup): static
  {
      if (!$this->conditionGroups->contains($conditionGroup)) {
          $this->conditionGroups->add($conditionGroup);
          $conditionGroup->setLine($this);
      }

      return $this;
  }

  public function removeConditionGroup(ConditionGroup $conditionGroup): static
  {
      if ($this->conditionGroups->removeElement($conditionGroup)) {
          // set the owning side to null (unless already changed)
          if ($conditionGroup->getLine() === $this) {
              $conditionGroup->setLine(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection<int, MethodLineVersion>
   */
  public function getMethodLineVersions(): Collection
  {
      return $this->methodLineVersions;
  }

  public function addMethodLineVersion(MethodLineVersion $methodLineVersion): static
  {
      if (!$this->methodLineVersions->contains($methodLineVersion)) {
          $this->methodLineVersions->add($methodLineVersion);
          $methodLineVersion->setLine($this);
      }

      return $this;
  }

  public function removeMethodLineVersion(MethodLineVersion $methodLineVersion): static
  {
      if ($this->methodLineVersions->removeElement($methodLineVersion)) {
          // set the owning side to null (unless already changed)
          if ($methodLineVersion->getLine() === $this) {
              $methodLineVersion->setLine(null);
          }
      }

      return $this;
  }

}
