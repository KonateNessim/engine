<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Common\BaseEntity;
use App\Repository\MethodLineVersionRepository;


#[ORM\Entity(repositoryClass: MethodLineVersionRepository::class)]
class MethodLineVersion extends BaseEntity
{
  #[ORM\ManyToOne(targetEntity: MethodLine::class)]
  private MethodLine $line;

  #[ORM\Column(length: 255)]
  private string $versionNumber;

  #[ORM\Column(type: 'json')]
  private array $snapshotJson = [];

  public function getLine(): MethodLine
  {
    return $this->line;
  }
  public function setLine(MethodLine $l): void
  {
    $this->line = $l;
  }
  public function getVersionNumber(): string
  {
    return $this->versionNumber;
  }
  public function setVersionNumber(string $v): void
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
