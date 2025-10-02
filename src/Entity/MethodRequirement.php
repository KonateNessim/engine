<?php

namespace App\Entity;

use App\Repository\MethodRequirementRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: MethodRequirementRepository::class)]
class MethodRequirement
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  private ?int $id = null;

  #[ORM\ManyToOne(targetEntity: Method::class)]
  private Method $method;

  #[ORM\Column(length: 255)]
  private string $label;

  #[ORM\Column(length: 100)]
  private string $code;

  #[ORM\ManyToOne(targetEntity: DataType::class)]
  private DataType $dataType;

  #[ORM\ManyToOne(targetEntity: ItemType::class)]
  private ItemType $itemType;

  #[ORM\Column(type: 'boolean')]
  private bool $isRequired = true;

  #[ORM\Column(type: 'json', nullable: true)]
  private ?array $defaultValue = null;

  #[ORM\Column(type: 'json', nullable: true)]
  private ?array $validationRules = null;

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
  public function getDataType(): DataType
  {
    return $this->dataType;
  }
  public function setDataType(DataType $d): void
  {
    $this->dataType = $d;
  }
  public function getItemType(): ItemType
  {
    return $this->itemType;
  }
  public function setItemType(ItemType $i): void
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
  public function getDefaultValue(): ?array
  {
    return $this->defaultValue;
  }
  public function setDefaultValue(?array $v): void
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
