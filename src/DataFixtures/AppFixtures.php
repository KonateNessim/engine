<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\{ VersionMethod, Branch, MethodLine, Place, ConditionGroup,Condition, EngineBranch, Method};
use App\Enum\{EngineStatus, Status, OperatorType};

class AppFixtures extends Fixture
{
  public function load(ObjectManager $em): void
  {
   

    $engine = new EngineBranch();
    $engine->setBranch(1);
    $engine->setName('Moteur Auto Particulier');
    $engine->setStatus(EngineStatus::ACTIVE);
    $em->persist($engine);
    foreach ([['VAR1', 'Montant 1'], ['VAR2', 'Montant 2'], ['VAR3', 'Diviseur'], ['AGE', 'Âge conducteur']] as $arr) {
      $a = new \App\Entity\Argument();
      $a->setName($arr[0]);
      $a->setLabel($arr[1]);
      $em->persist($a);
    }
    $method = new Method();
    $method->setName('Calcul Prime Auto');
    $method->setCode('CALC_PRIME_AUTO');
    $method->setEngine($engine);
    $em->persist($method);
    $vm = new VersionMethod();
    $vm->setMethod($method);
    $vm->setVersionNumber('v1');
    $vm->setIsActive(true);
    $em->persist($vm);
    // Line 1: (VAR1 + VAR2) / VAR3 -> prime_base si AGE > 25
    $l1 = new MethodLine();
    $l1->setVersionMethod($vm);
    $l1->setOrderIndex(1);
    $l1->setResultVariable('prime_base');
    $em->persist($l1);
    $p1 = new Place();
    $p1->setLine($l1);
    $p1->setOrderIndex(1);
    $p1->setArgument($em->getRepository(\App\Entity\Argument::class)->findOneBy(['name' => 'VAR1']));
    $em->persist($p1);
    $p2 = new Place();
    $p2->setLine($l1);
    $p2->setOrderIndex(2);
    $p2->setOperator(OperatorType::from('+'));
    $em->persist($p2);
    $p3 = new Place();
    $p3->setLine($l1);
    $p3->setOrderIndex(3);
    $p3->setArgument($em->getRepository(\App\Entity\Argument::class)->findOneBy(['name' => 'VAR2']));
    $em->persist($p3);
    $p4 = new Place();
    $p4->setLine($l1);
    $p4->setOrderIndex(4);
    $p4->setOperator(OperatorType::from('/'));
    $em->persist($p4);
    $p5 = new Place();
    $p5->setLine($l1);
    $p5->setOrderIndex(5);
    $p5->setArgument($em->getRepository(\App\Entity\Argument::class)->findOneBy(['name' => 'VAR3']));
    $em->persist($p5);
    // Condition group: AGE > 25
    $g1 = new ConditionGroup();
    $g1->setLine($l1);
    $g1->setLogicOperator('AND');
    $g1->setOrderIndex(1);
    $em->persist($g1);
    $c1 = new Condition();
    $c1->setGroup($g1);
    $c1->setOrderIndex(1);
    $c1->setLeftArgument($em->getRepository(\App\Entity\Argument::class)->findOneBy(['name' => 'AGE']));
    $c1->setOperator(OperatorType::from('>'));
    $c1->setRightValue('25');
    $em->persist($c1);
    $em->flush();
  }
}
