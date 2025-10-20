<?php

namespace App\Entity;

use App\Entity\Common\TraitEntity;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\EngineStatus;
use App\Repository\EngineBranchRepository;
use Random\Engine;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EngineBranchRepository::class)]
class EngineBranch 
{
use TraitEntity;

#[ORM\Id, ORM\GeneratedValue, ORM\Column(type:'integer')] private ?int $id=null;

  #[ORM\Column(length: 255)]
  #[Groups(['method'])]
  private ?int $branch;

  #[ORM\Column(length: 255)]
  #[Groups(['method'])]
  private string $name;

  #[ORM\Column(type: 'text', nullable: true)]
  #[Groups(['method'])]
  private ?string $description = null;

  #[ORM\Column(type: 'string', enumType: EngineStatus::class)]
  #[Groups(['method'])]
  private EngineStatus $status = EngineStatus::DRAFT;

  public function getId(): ?int {return $this->id;}

  public function getBranch(): ?int
  {
    return $this->branch;
  }
  public function setBranch(?int $b): void
  {
    $this->branch = $b;
  }

  public function getName(): string
  {
    return $this->name;
  }
  public function setName(string $n): void
  {
    $this->name = $n;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }
  public function setDescription(?string $d): void
  {
    $this->description = $d;
  }

  public function getStatus(): EngineStatus
  {
    return $this->status;
  }
  public function setStatus(EngineStatus $s): void
  {
    $this->status = $s;
  }
}
