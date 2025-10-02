<?php
namespace App\Entity;

use App\Repository\ArgumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArgumentRepository::class)]
class Argument {
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:'integer')]
  private ?int $id=null;

  #[ORM\Column(length:100)]
  private string $name;

  #[ORM\Column(length:255, nullable:true)]
  private ?string $label=null;

  #[ORM\Column(length:50)]
  private string $type='float';

  #[ORM\Column(type:'json', nullable:true)]
  private ?array $defaultValue=null;

  #[ORM\Column(type:'boolean')]
  private bool $isRequired=true;

  #[ORM\Column(type:'json', nullable:true)]
  private ?array $constraints=null;

  public function getId(): ?int { return $this->id; }
  public function getName(): string { return $this->name; }
  public function setName(string $n): void { $this->name=$n; }
  public function getLabel(): ?string { return $this->label; }
  public function setLabel(?string $l): void { $this->label=$l; }
  public function getType(): string { return $this->type; }
  public function setType(string $t): void { $this->type=$t; }
  public function getDefaultValue(): ?array { return $this->defaultValue; }
  public function setDefaultValue(?array $v): void { $this->defaultValue=$v; }
  public function isRequired(): bool { return $this->isRequired; }
  public function setIsRequired(bool $r): void { $this->isRequired=$r; }
  public function getConstraints(): ?array { return $this->constraints; }
  public function setConstraints(?array $c): void { $this->constraints=$c; }
}