<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Common\TraitEntity;
use App\Repository\MethodLineVersionRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MethodLineVersionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MethodLineVersion
{
  use TraitEntity;

  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')] 
  #[Groups(['methodLine'])]
  private ?int $id = null;

  #[ORM\Column(type: 'integer')]
  #[Groups(['methodLine'])]
  private int $versionNumber;

  #[ORM\Column(type: 'json')] 
  #[Groups(['methodLine'])]
  private array $snapshotJson = [];

  #[ORM\ManyToOne(inversedBy: 'methodLineVersions')]
  private ?MethodLine $line = null;

  #[ORM\Column(length: 255, nullable: true)]
  #[Groups(['methodLine'])]
  private ?string $versionName = null;

  public function getId(): ?int
  {
    return $this->id;
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

  public function getLine(): ?MethodLine
  {
      return $this->line;
  }

  public function setLine(?MethodLine $line): static
  {
      $this->line = $line;

      return $this;
  }

  public function getVersionName(): ?string
  {
      return $this->versionName;
  }

  public function setVersionName(?string $versionName): static
  {
      $this->versionName = $versionName;

      return $this;
  }
}
