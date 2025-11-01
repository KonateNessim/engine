<?php

namespace App\Entity;

use App\Repository\MethodRequirementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MethodRequirementRepository::class)]
class MethodRequirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["method_requirement"])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Method::class, inversedBy: 'requirements')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(["method_requirement"])]
    private ?Method $method = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["method_requirement"])]
    private string $label;

    #[ORM\ManyToOne(targetEntity: Argument::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(["method_requirement"])]
    private ?Argument $code = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(["method_requirement"])]
    private bool $isRequired = true;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(["method_requirement"])]
    private mixed $defaultValue = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(["method_requirement"])]
    private ?array $validationRules = null;

    // --- Getters & Setters ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMethod(): ?Method
    {
        return $this->method;
    }

    public function setMethod(?Method $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getCode(): ?Argument
    {
        return $this->code;
    }

    public function setCode(?Argument $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(mixed $defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getValidationRules(): ?array
    {
        return $this->validationRules;
    }

    public function setValidationRules(?array $validationRules): self
    {
        $this->validationRules = $validationRules;
        return $this;
    }
}
