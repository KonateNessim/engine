<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Common\BaseEntity;
use App\Enum\EngineStatus;
use App\Repository\EngineBranchRepository;
use Random\Engine;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EngineBranchRepository::class)]
class EngineBranch extends BaseEntity
{

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
