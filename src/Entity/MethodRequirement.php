<?php

namespace App\Entity;

use App\Repository\MethodRequirementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MethodRequirementRepository::class)]
class MethodRequirement
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  #[Groups(["group_requirements"])]
  private ?int $id = null;

  #[ORM\ManyToOne(targetEntity: Method::class)]
  #[Groups(["group_requirements"])]
  private Method $method;

  #[ORM\Column(length: 255)]
  #[Groups(["group_requirements"])]
  private string $label;

  #[ORM\Column(length: 100)]
  #[Groups(["group_requirements"])]
  private string $code;

  #[ORM\ManyToOne(targetEntity: DataType::class)]
  #[Groups(["group_requirements"])]
  private ?DataType $dataType = null;

  #[ORM\ManyToOne(targetEntity: ItemType::class)]
  #[Groups(["group_requirements"])]
  private ?ItemType $itemType = null;

  #[ORM\Column(type: 'boolean')]
  private bool $isRequired = true;

  #[ORM\Column(type: 'json', nullable: true)]
  private mixed $defaultValue = null; // Peut être string, int, bool, array, etc.

  #[ORM\Column(type: 'json', nullable: true)]
  private ?array $validationRules = null; // ex: {"min":18,"max":80}

  // -------- Getters / Setters --------

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getMethod(): Method
  {
    return $this->method;
  }
  public function setMethod(Method $m): void
  {
    $this->method = $m;
  }

  public function getLabel(): string
  {
    return $this->label;
  }
  public function setLabel(string $l): void
  {
    $this->label = $l;
  }

  public function getCode(): string
  {
    return $this->code;
  }
  public function setCode(string $c): void
  {
    $this->code = $c;
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
  public function getValidationRules(): ?array
  {
    return $this->validationRules;
  }
  public function setValidationRules(?array $v): void
  {
    $this->validationRules = $v;
  }
}
