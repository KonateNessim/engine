<?php
namespace App\Service;
use App\Entity\{Method, MethodLine, Place, ConditionGroup, Condition};
use Doctrine\ORM\EntityManagerInterface;
class MethodCompiler {
  public function __construct(private EntityManagerInterface $em){}
  public function compile(Method $method): array {
    $lines = $this->em->getRepository(MethodLine::class)->findBy(['method'=>$method], ['orderIndex'=>'ASC']);
    $compiled=[];
    foreach ($lines as $ln) {
      $places = $this->em->getRepository(Place::class)->findBy(['line'=>$ln], ['orderIndex'=>'ASC']);
      $groups = $this->em->getRepository(ConditionGroup::class)->findBy(['line'=>$ln], ['orderIndex'=>'ASC']);
      $conditions=[];
      foreach ($groups as $g) {
        $conds = $this->em->getRepository(Condition::class)->findBy(['group'=>$g], ['orderIndex'=>'ASC']);
        $conditions[] = ['group'=>$g->getId(),'logic'=>$g->getLogicOperator(),'conditions'=>array_map(fn($c)=>[
          'left'=>$c->getLeftArgument()?->getName(),'op'=>$c->getOperator()->value,'right'=>$c->getRightValue(),'order'=>$c->getOrderIndex()
        ], $conds)];
      }
      $compiled[] = [
        'id'=>$ln->getId(),'order'=>$ln->getOrderIndex(),'result'=>$ln->getResultVariable(),'type'=>$ln->getLineType(),'expression'=>$ln->getExpression(),
        'places'=>array_map(fn($p)=>['order'=>$p->getOrderIndex(),'op'=>$p->getOperator()?->value,'arg'=>$p->getArgument()?->getName(),'val'=>$p->getLiteralValue()], $places),
        'groups'=>$conditions,'metadata'=>$ln->getMetadata()
      ];
    }
    return ['methodId'=>$method->getId(),'lines'=>$compiled];
  }
}
