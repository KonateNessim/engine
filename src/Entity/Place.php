<?php

namespace App\Entity;

use App\Enum\OperatorType;
use App\Enum\ParenthesisType;
use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Groups(['methodLine'])]
    private ?int $id = null;


    #[ORM\ManyToOne(targetEntity: Argument::class)]
    #[Groups(['methodLine'])]
    private ?Argument $argument = null;

    #[ORM\Column(type: 'string', enumType: OperatorType::class, nullable: true)]
    #[Groups(['methodLine'])]
    private ?OperatorType $operator = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['methodLine'])]
    private ?string $literalValue = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['methodLine'])]
    private int $orderIndex = 1;

    #[ORM\ManyToOne(inversedBy: 'places')]
    private ?MethodLine $line = null;


    public function getId(): ?int { return $this->id; }

 

    public function getArgument(): ?Argument { return $this->argument; }
    public function setArgument(?Argument $a): void { $this->argument = $a; }

    public function getOperator(): ?OperatorType { return $this->operator; }
    public function setOperator(?OperatorType $o): void { $this->operator = $o; }

    public function getLiteralValue(): ?string { return $this->literalValue; }
    public function setLiteralValue(?string $v): void { $this->literalValue = $v; }

    public function getOrderIndex(): int { return $this->orderIndex; }
    public function setOrderIndex(int $i): void { $this->orderIndex = $i; }

    public function getLine(): ?MethodLine
    {
        return $this->line;
    }

    public function setLine(?MethodLine $line): static
    {
        $this->line = $line;

        return $this;
    }

    
}
