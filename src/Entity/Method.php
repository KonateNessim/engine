<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Common\BaseEntity;
use App\Repository\MethodRepository;


#[ORM\Entity(repositoryClass: MethodRepository::class)]
class Method extends BaseEntity
{
  #[ORM\ManyToOne(targetEntity: EngineBranch::class)]
  private ?EngineBranch $engine = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?int $insurer = null;

/*   #[ORM\ManyToOne(targetEntity: Method::class)]
  private ?Method $originMethod = null; */

  #[ORM\Column(length: 255)]
  private string $name;

  #[ORM\Column(length: 100)]
  private string $code;

  #[ORM\Column(type: 'text', nullable: true)]
  private ?string $description = null;

  #[ORM\Column(length: 50)]
  private string $returnType = 'float';

  #[ORM\Column(length: 50)]
  private string $category = 'calculation';

  #[ORM\Column(type: 'boolean')]
  private bool $isImmutable = false;

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

/*   public function getOriginMethod(): ?Method
  {
    return $this->originMethod;
  }
  public function setOriginMethod(?Method $m): void
  {
    $this->originMethod = $m;
  } */

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
}
