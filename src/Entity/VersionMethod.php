<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Common\BaseEntity;
use App\Repository\VersionMethodRepository;


#[ORM\Entity(repositoryClass: VersionMethodRepository::class)]
class VersionMethod extends BaseEntity
{
  #[ORM\ManyToOne(targetEntity: Method::class)]
  private Method $method;

  #[ORM\Column(length: 50)]
  private string $versionNumber = 'v1';

 /*  #[ORM\Column(type: 'boolean')]
  private bool $isActive = true; */

  #[ORM\Column(type: 'text', nullable: true)]
  private ?string $description = null;

  #[ORM\Column(type: 'integer', nullable: true)]
  private ?int $orderIndex = null;

  public function getMethod(): Method
  {
    return $this->method;
  }
  public function setMethod(Method $m): void
  {
    $this->method = $m;
  }
  public function getVersionNumber(): string
  {
    return $this->versionNumber;
  }
  public function setVersionNumber(string $v): void
  {
    $this->versionNumber = $v;
  }
/*   public function isActive(): bool
  {
    return $this->isActive;
  }
  public function setIsActive(bool $a): void
  {
    $this->isActive = $a;
  } */
  public function getDescription(): ?string
  {
    return $this->description;
  }
  public function setDescription(?string $d): void
  {
    $this->description = $d;
  }
  public function getOrderIndex(): ?int
  {
    return $this->orderIndex;
  }
  public function setOrderIndex(?int $i): void
  {
    $this->orderIndex = $i;
  }
}
