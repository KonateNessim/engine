<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ArgumentRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ArgumentRepository::class)]
class Argument
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  #[Groups(["argument","method_requirement"])]
  private ?int $id = null;

  #[ORM\Column(length: 100)]
  #[Groups(["argument","method_requirement"])]
  private string $name;

  #[ORM\Column(length: 255, nullable: true)]
  #[Groups(["argument","method_requirement"])]
  private ?string $label = null;

  #[ORM\ManyToOne(targetEntity: DataType::class)]
  #[Groups(["argument"])]
  private ?DataType $dataType = null;

  #[ORM\ManyToOne(targetEntity: ItemType::class)]
  #[Groups(["argument"])]
  private ?ItemType $itemType = null;

  #[ORM\Column(type: 'boolean')]
  #[Groups(["argument"])]
  private bool $isRequired = true;

  #[ORM\Column(type: 'json', nullable: true)]
  #[Groups(["argument"])]
  private mixed $defaultValue = null;

  #[ORM\Column(type: 'json', nullable: true)]
  #[Groups(["argument"])]
  private ?array $constraints = null;

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getName(): string
  {
    return $this->name;
  }
  public function setName(string $n): void
  {
    $this->name = $n;
  }
  public function getLabel(): ?string
  {
    return $this->label;
  }
  public function setLabel(?string $l): void
  {
    $this->label = $l;
  }
  public function getDataType(): ?DataType
  {
    return $this->dataType;
  }
  public function setDataType(?DataType $d): void
  {
    $this->dataType = $d;
  }
  public function getItemType(): ?ItemType
  {
    return $this->itemType;
  }
  public function setItemType(?ItemType $i): void
  {
    $this->itemType = $i;
  }
  public function isRequired(): bool
  {
    return $this->isRequired;
  }
  public function setIsRequired(bool $r): void
  {
    $this->isRequired = $r;
  }
  public function getDefaultValue(): mixed
  {
    return $this->defaultValue;
  }
  public function setDefaultValue(mixed $v): void
  {
    $this->defaultValue = $v;
  }
  public function getConstraints(): ?array
  {
    return $this->constraints;
  }
  public function setConstraints(?array $c): void
  {
    $this->constraints = $c;
  }
}
