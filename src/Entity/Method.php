<?php

namespace App\Entity;

use App\Entity\Common\TraitEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MethodRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MethodRepository::class)]
#[ORM\Table(
  name: 'method',
  uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_code_engine', columns: ['code', 'engine_id'])
  ]
)]
class Method
{
  use TraitEntity;

  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  #[Groups(["method"])]
  private ?int $id = null;
  #[ORM\ManyToOne(targetEntity: EngineBranch::class)]
  #[Groups(["method"])]
  private ?EngineBranch $engine = null;

  #[ORM\Column(length: 255, nullable: true)]
  #[Groups(["method"])]
  private ?string $insurer = null;

  #[ORM\Column(length: 255)]
  #[Groups(["method"])]
  private string $name;

  #[ORM\Column(length: 100)]
  #[Groups(["method"])]
  private string $code;

  #[ORM\Column(type: 'text', nullable: true)]
  #[Groups(["method"])]
  private ?string $description = null;

  #[ORM\Column(length: 50)]
  private string $returnType = 'float';

  #[ORM\Column(length: 50)]
  #[Groups(["method"])]
  private string $category = 'calculation';

  #[ORM\Column(type: 'boolean')]
  #[Groups(["method"])]
  private bool $isImmutable = false;

  /**
   * @var Collection<int, MethodLine>
   */
  #[ORM\OneToMany(targetEntity: MethodLine::class, mappedBy: 'method')]
  private Collection $methodLines;

  #[ORM\Column(length: 255, nullable: true)]
  #[Groups(["method"])]
  private ?string $publicName = null;

  public function __construct()
  {
    /*  parent::__construct(); */
    $this->methodLines = new ArrayCollection();
  }
  public function getId(): ?int
  {
    return $this->id;
  }

  public function getEngine(): ?EngineBranch
  {
    return $this->engine;
  }
  public function setEngine(?EngineBranch $e): void
  {
    $this->engine = $e;
  }

  public function getInsurer(): ?string
  {
    return $this->insurer;
  }
  public function setInsurer(?string $i): void
  {
    $this->insurer = $i;
  }

  public function getName(): string
  {
    return $this->name;
  }
  public function setName(string $n): void
  {
    $this->name = $n;
  }

  public function getCode(): string
  {
    return $this->code;
  }
  public function setCode(string $c): void
  {
    $this->code = $c;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }
  public function setDescription(?string $d): void
  {
    $this->description = $d;
  }

  public function getReturnType(): string
  {
    return $this->returnType;
  }
  public function setReturnType(string $t): void
  {
    $this->returnType = $t;
  }

  public function getCategory(): string
  {
    return $this->category;
  }
  public function setCategory(string $c): void
  {
    $this->category = $c;
  }

  public function isImmutable(): bool
  {
    return $this->isImmutable;
  }
  public function setIsImmutable(bool $i): void
  {
    $this->isImmutable = $i;
  }

  /**
   * @return Collection<int, MethodLine>
   */
  public function getMethodLines(): Collection
  {
    return $this->methodLines;
  }

  public function addMethodLine(MethodLine $methodLine): static
  {
    if (!$this->methodLines->contains($methodLine)) {
      $this->methodLines->add($methodLine);
      $methodLine->setMethod($this);
    }

    return $this;
  }

  public function removeMethodLine(MethodLine $methodLine): static
  {
    if ($this->methodLines->removeElement($methodLine)) {
      // set the owning side to null (unless already changed)
      if ($methodLine->getMethod() === $this) {
        $methodLine->setMethod(null);
      }
    }

    return $this;
  }

  public function getPublicName(): ?string
  {
    return $this->publicName;
  }

  public function setPublicName(?string $publicName): static
  {
    $this->publicName = $publicName;

    return $this;
  }
}
