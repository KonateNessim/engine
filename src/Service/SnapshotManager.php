<?php

namespace App\Service;

use App\Entity\{MethodLine, MethodLineVersion, Place, ConditionGroup, Condition};
use Doctrine\ORM\EntityManagerInterface;

class SnapshotManager
{
  public function __construct(private EntityManagerInterface $em) {}
  public function createSnapshot(MethodLine $line): MethodLineVersion
  {
    $last = $this->em->getRepository(MethodLineVersion::class)->findOneBy(['line' => $line], ['versionNumber' => 'DESC']);
    $next = ($last?->getVersionNumber() ?? 0) + 1;
    $places = $this->em->getRepository(Place::class)->findBy(['line' => $line], ['orderIndex' => 'ASC']);
    $groups = $this->em->getRepository(ConditionGroup::class)->findBy(['line' => $line], ['orderIndex' => 'ASC']);
    $groupJson = [];
    foreach ($groups as $g) {
      $conds = $this->em->getRepository(Condition::class)->findBy(['group' => $g], ['orderIndex' => 'ASC']);
      $groupJson[] = ['logic' => $g->getLogicOperator(), 'order' => $g->getOrderIndex(), 'conditions' => array_map(fn($c) => [
        'left' => $c->getLeftArgument()?->getName(),
        'op' => $c->getOperator()->value,
        'right' => $c->getRightValue(),
        'order' => $c->getOrderIndex()
      ], $conds)];
    }
    $snapshot = [
      'line' => [
        'orderIndex' => $line->getOrderIndex(),
        'resultVariable' => $line->getResultVariable(),
        'lineType' => $line->getLineType(),
        'expression' => $line->getExpression(),
        'metadata' => $line->getMetadata()
      ],
      'places' => array_map(fn($p) => ['order' => $p->getOrderIndex(), 'op' => $p->getOperator()?->value, 'arg' => $p->getArgument()?->getName(), 'val' => $p->getValue()], $places),
      'groups' => $groupJson
    ];
    $ver = new MethodLineVersion();
    $ver->setLine($line);
    $ver->setVersionNumber($next);
    $ver->setSnapshotJson($snapshot);
    $this->em->persist($ver);
    return $ver;
  }
}
