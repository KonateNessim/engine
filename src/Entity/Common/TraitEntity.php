<?php

namespace App\Entity\Common;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait TraitEntity
{


    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['methodLine'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['methodLine'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdBy = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedBy = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['methodLine'])]
    private bool $isActive = true;

    public function initializeTimestamps(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

   

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $d): self
    {
        $this->createdAt = $d;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $d): self
    {
        $this->updatedAt = $d;
        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $id): self
    {
        $this->createdBy = $id;
        return $this;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?int $id): self
    {
        $this->updatedBy = $id;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $a): self
    {
        $this->isActive = $a;
        return $this;
    }
}
