<?php

namespace App\Entity;

use App\Repository\ItemTypeRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ItemTypeRepository::class)]
class ItemType
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  private ?int $id = null;

  #[ORM\Column(length: 100)]
  private string $name;

  #[ORM\Column(length: 255)]
  private string $label;

  #[ORM\Column(type: 'json', nullable: true)]
  private ?array $options = null;

  #[ORM\Column(type: 'json', nullable: true)]
  private ?array $validation = null;

  #[ORM\Column(length: 50)]
  private string $inputType;

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
  public function getLabel(): string
  {
    return $this->label;
  }
  public function setLabel(string $l): void
  {
    $this->label = $l;
  }
  public function getOptions(): ?array
  {
    return $this->options;
  }
  public function setOptions(?array $o): void
  {
    $this->options = $o;
  }
  public function getValidation(): ?array
  {
    return $this->validation;
  }
  public function setValidation(?array $v): void
  {
    $this->validation = $v;
  }
  public function getInputType(): string
  {
    return $this->inputType;
  }
  public function setInputType(string $i): void
  {
    $this->inputType = $i;
  }
}
