<?php

namespace App\Entity;

use App\Repository\DataTypeRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: DataTypeRepository::class)]
class DataType
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  private ?int $id = null;

  #[ORM\Column(length: 100)]
  private string $name;

  #[ORM\Column(length: 50)]
  private string $phpType;

  #[ORM\Column(length: 50)]
  private string $jsType;

  #[ORM\Column(type: 'json', nullable: true)]
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
  public function getPhpType(): string
  {
    return $this->phpType;
  }
  public function setPhpType(string $t): void
  {
    $this->phpType = $t;
  }
  public function getJsType(): string
  {
    return $this->jsType;
  }
  public function setJsType(string $t): void
  {
    $this->jsType = $t;
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
