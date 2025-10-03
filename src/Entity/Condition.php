<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\OperatorType;
use App\Repository\ConditionRepository;

#[ORM\Table(name: "conditions")]
#[ORM\Entity(repositoryClass: ConditionRepository::class)]
class Condition
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ConditionGroup::class)]
    private ConditionGroup $group;

    #[ORM\ManyToOne(targetEntity: Argument::class)]
    private ?Argument $leftArgument = null;

    #[ORM\Column(type: 'string', enumType: OperatorType::class)]
    private OperatorType $operator = OperatorType::Equal;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $rightValue = null;

    #[ORM\Column(type: 'integer')]
    private int $orderIndex = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): ConditionGroup
    {
        return $this->group;
    }

    public function setGroup(ConditionGroup $g): void
    {
        $this->group = $g;
    }

    public function getLeftArgument(): ?Argument
    {
        return $this->leftArgument;
    }

    public function setLeftArgument(?Argument $a): void
    {
        $this->leftArgument = $a;
    }

    public function getOperator(): OperatorType
    {
        return $this->operator;
    }

    public function setOperator(OperatorType $o): void
    {
        $this->operator = $o;
    }

    public function getRightValue(): ?string
    {
        return $this->rightValue;
    }

    public function setRightValue(?string $v): void
    {
        $this->rightValue = $v;
    }

    public function getOrderIndex(): int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $i): void
    {
        $this->orderIndex = $i;
    }

    /**
     * Proxy pratique : permet d'accéder directement à la MethodLine
     * via $condition->getLine() au lieu de $condition->getGroup()->getLine()
     */
    public function getLine(): ?MethodLine
    {
        return $this->group ? $this->group->getLine() : null;
    }
}
