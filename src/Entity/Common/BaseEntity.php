<?php

namespace App\Entity\Common;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class BaseEntity
{
  #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
  private ?int $id = null;



  #[ORM\Column(type: 'datetime_immutable', nullable: true)]
  private ?\DateTimeImmutable $createdAt = null;

  #[ORM\Column(type: 'datetime_immutable', nullable: true)]
  private ?\DateTimeImmutable $updatedAt = null;

  #[ORM\Column(type: 'integer', nullable: true)]
  private ?int $createdBy = null;

  #[ORM\Column(type: 'integer', nullable: true)]
  private ?int $updatedBy = null;

  #[ORM\Column(type: 'boolean')]
  private bool $isActive = true;

  public function __construct()
  {
    $this->createdAt = new \DateTimeImmutable();
  }

  public function getId(): ?int
  {
    return $this->id;
  }
  public function getCreatedAt(): \DateTimeImmutable
  {
    return $this->createdAt;
  }
  public function setCreatedAt( $d): void
  {
    $this->createdAt = $d;
  }
  public function getUpdatedAt(): ?\DateTimeImmutable
  {
    return $this->updatedAt;
  }
  public function setUpdatedAt( $d): void
  {
    $this->updatedAt = $d;
  }
  public function getCreatedBy(): ?int
  {
    return $this->createdBy;
  }
  public function setCreatedBy(?int $id): void
  {
    $this->createdBy = $id;
  }
  public function getUpdatedBy(): ?int
  {
    return $this->updatedBy;
  }
  public function setUpdatedBy(?int $id): void
  {
    $this->updatedBy = $id;
  }
  public function isActive(): bool
  {
    return $this->isActive;
  }
  public function setIsActive(bool $a): void
  {
    $this->isActive = $a;
  }
}
