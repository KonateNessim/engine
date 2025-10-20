<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Common\TraitEntity;
use App\Repository\MethodLineVersionRepository;

#[ORM\Entity(repositoryClass: MethodLineVersionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MethodLineVersion
{
  use TraitEntity;

  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')] private ?int $id = null;
  #[ORM\ManyToOne(targetEntity: MethodLine::class)] private MethodLine $line;
  #[ORM\Column(type: 'integer')] private int $versionNumber;
  #[ORM\Column(type: 'json')] private array $snapshotJson = [];

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getLine(): MethodLine
  {
    return $this->line;
  }
  public function setLine(MethodLine $l): void
  {
    $this->line = $l;
  }
  public function getVersionNumber(): int
  {
    return $this->versionNumber;
  }
  public function setVersionNumber(int $v): void
  {
    $this->versionNumber = $v;
  }
  public function getSnapshotJson(): array
  {
    return $this->snapshotJson;
  }
  public function setSnapshotJson(array $s): void
  {
    $this->snapshotJson = $s;
  }
}
