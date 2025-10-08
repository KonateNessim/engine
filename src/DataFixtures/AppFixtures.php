<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;

use App\Entity\{
    EngineBranch, Method,
    MethodLine, Argument, Place, ConditionGroup, Condition,
    ItemType, DataType
};
use App\Enum\OperatorType;
use App\Enum\EngineStatus;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $em): void
    {
        // ---------------- TYPES ----------------
        $dataTypes = [];
        foreach ([
            ["integer","int","number",[]],
            ["float","float","number",[]],
            ["string","string","text",[]],
            ["date","\\DateTime","date",[]],
        ] as [$name,$php,$js,$constraints]) {
            $dt = new DataType();
            $dt->setName($name);
            $dt->setPhpType($php);
            $dt->setJsType($js);
            $dt->setConstraints($constraints);
            $em->persist($dt);
            $dataTypes[$name] = $dt;
        }

        $itemTypes = [];
        foreach ([
            ["input","Champ texte","text",[],[]],
            ["select","Choix déroulant","select",["EU","AFR","ASIA","US"],[]],
            ["number","Champ numérique","number",[],[]],
            ["date","Date","date",[],[]],
        ] as [$name,$label,$input,$options,$validation]) {
            $it = new ItemType();
            $it->setName($name);
            $it->setLabel($label);
            $it->setInputType($input);
            $it->setOptions($options);
            $it->setValidation($validation);
            $em->persist($it);
            $itemTypes[$name] = $it;
        }

        // ================== ENGINE AUTO ==================
        $engineAuto = new EngineBranch();
        $engineAuto->setBranch(1);
        $engineAuto->setName('Moteur Auto Particulier');
        $engineAuto->setIsActive(true);
        $engineAuto->setStatus(EngineStatus::ACTIVE);
        $em->persist($engineAuto);

        $methodAuto = new Method();
        $methodAuto->setEngine($engineAuto);
        $methodAuto->setInsurer(10);
        $methodAuto->setName('Calcul Prime Auto');
        $methodAuto->setCode('CALC_PRIME_AUTO');
        $em->persist($methodAuto);

        // Arguments AUTO
        $argsAuto = [];
        foreach (["VAR1","VAR2","VAR3","AGE","LOYALTY_YEARS"] as $n) {
            $a = new Argument();
            $a->setName($n);
            $a->setType('integer');
            $a->setIsRequired(true);
            $em->persist($a);
            $argsAuto[$n] = $a;
        }

        // Ligne 1 : prime_base = (VAR1 + VAR2) / VAR3
        $l1 = new MethodLine();
        $l1->setMethod($methodAuto);
        $l1->setOrderIndex(1);
        $l1->setResultVariable('prime_base');
        $l1->setLineType('calculation');
        $em->persist($l1);

        $p1 = new Place(); $p1->setLine($l1); $p1->setOperator(OperatorType::LParen); $p1->setOrderIndex(1); $em->persist($p1);
        $p2 = new Place(); $p2->setLine($l1); $p2->setArgument($argsAuto["VAR1"]); $p2->setOrderIndex(2); $em->persist($p2);
        $p3 = new Place(); $p3->setLine($l1); $p3->setOperator(OperatorType::Plus); $p3->setOrderIndex(3); $em->persist($p3);
        $p4 = new Place(); $p4->setLine($l1); $p4->setArgument($argsAuto["VAR2"]); $p4->setOrderIndex(4); $em->persist($p4);
        $p5 = new Place(); $p5->setLine($l1); $p5->setOperator(OperatorType::RParen); $p5->setOrderIndex(5); $em->persist($p5);
        $p6 = new Place(); $p6->setLine($l1); $p6->setOperator(OperatorType::Divide); $p6->setOrderIndex(6); $em->persist($p6);
        $p7 = new Place(); $p7->setLine($l1); $p7->setArgument($argsAuto["VAR3"]); $p7->setOrderIndex(7); $em->persist($p7);

        $cg1 = new ConditionGroup();
        $cg1->setLine($l1);
        $cg1->setLogicOperator('AND');
        $cg1->setOrderIndex(1);
        $em->persist($cg1);

        $c1 = new Condition();
        $c1->setGroup($cg1);
        $c1->setLeftArgument($argsAuto["AGE"]);
        $c1->setOperator(OperatorType::GreaterThan);
        $c1->setRightValue('25');
        $em->persist($c1);

        // Ligne 2 : prime_finale = prime_base * 0.9 si LOYALTY_YEARS >= 5
        $l2 = new MethodLine();
        $l2->setMethod($methodAuto);
        $l2->setOrderIndex(2);
        $l2->setResultVariable('prime_finale');
        $l2->setExpression('prime_base * 0.9');
        $l2->setLineType('calculation');
        $em->persist($l2);

        $cg2 = new ConditionGroup();
        $cg2->setLine($l2);
        $cg2->setLogicOperator('AND');
        $cg2->setOrderIndex(1);
        $em->persist($cg2);

        $c2 = new Condition();
        $c2->setGroup($cg2);
        $c2->setLeftArgument($argsAuto["LOYALTY_YEARS"]);
        $c2->setOperator(OperatorType::GreaterOrEqual);
        $c2->setRightValue('5');
        $em->persist($c2);

        // ================== ENGINE SANTÉ ==================
        $engineSante = new EngineBranch();
        $engineSante->setBranch(2);
        $engineSante->setName('Moteur Santé Individuelle');
        $engineSante->setIsActive(true);
        $engineSante->setStatus(EngineStatus::ACTIVE);
        $em->persist($engineSante);

        $methodSante = new Method();
        $methodSante->setEngine($engineSante);
        $methodSante->setName('Calcul Prime Santé');
        $methodSante->setCode('CALC_PRIME_SANTE');
        $em->persist($methodSante);

        $argsSante = [];
        foreach (["AGE_ASSURE","NB_ENFANTS","PATHOLOGIES"] as $n) {
            $a = new Argument();
            $a->setName($n);
            $a->setType('integer');
            $a->setIsRequired(false);
            $em->persist($a);
            $argsSante[$n] = $a;
        }

        $lS1 = new MethodLine();
        $lS1->setMethod($methodSante);
        $lS1->setOrderIndex(1);
        $lS1->setResultVariable('prime_sante');
        $lS1->setExpression('(AGE_ASSURE * 1.2) + (NB_ENFANTS * 0.5)');
        $lS1->setLineType('calculation');
        $em->persist($lS1);

        $cgS1 = new ConditionGroup();
        $cgS1->setLine($lS1);
        $cgS1->setLogicOperator('AND');
        $cgS1->setOrderIndex(1);
        $em->persist($cgS1);

        $cS1 = new Condition();
        $cS1->setGroup($cgS1);
        $cS1->setLeftArgument($argsSante["PATHOLOGIES"]);
        $cS1->setOperator(OperatorType::LessOrEqual);
        $cS1->setRightValue('0');
        $em->persist($cS1);

        // ================== ENGINE VOYAGE ==================
        $engineVoyage = new EngineBranch();
        $engineVoyage->setBranch(3);
        $engineVoyage->setName('Moteur Voyage International');
        $engineVoyage->setIsActive(true);
        $engineVoyage->setStatus(EngineStatus::ACTIVE);
        $em->persist($engineVoyage);

        $methodVoyage = new Method();
        $methodVoyage->setEngine($engineVoyage);
        $methodVoyage->setInsurer(20);
        $methodVoyage->setName('Calcul Prime Voyage');
        $methodVoyage->setCode('CALC_PRIME_VOYAGE');
        $em->persist($methodVoyage);

        $argsVoyage = [];
        foreach (["DUREE","DESTINATION_RISQUE","AGE_VOYAGEUR"] as $n) {
            $a = new Argument();
            $a->setName($n);
            $a->setType('integer');
            $a->setIsRequired(false);
            $em->persist($a);
            $argsVoyage[$n] = $a;
        }

        $lV1 = new MethodLine();
        $lV1->setMethod($methodVoyage);
        $lV1->setOrderIndex(1);
        $lV1->setResultVariable('prime_voyage');
        $lV1->setExpression('(DUREE * 15) + (DESTINATION_RISQUE == 1 ? 50 : 0)');
        $em->persist($lV1);

        // ================== MÉTHODE GLOBALE CALCUL ÂGE ==================
        $methodAge = new Method();
        $methodAge->setName('Calcul Age');
        $methodAge->setCode('CALC_AGE');
        $em->persist($methodAge);

        $argDateNaissance = new Argument();
        $argDateNaissance->setName("DATE_NAISSANCE");
        $argDateNaissance->setType("date");
        $argDateNaissance->setIsRequired(true);
        $em->persist($argDateNaissance);

        $lAge = new MethodLine();
        $lAge->setMethod($methodAge);
        $lAge->setOrderIndex(1);
        $lAge->setResultVariable("AGE");
        $lAge->setExpression("(now().format('Y') - DATE_NAISSANCE.format('Y'))");
        $em->persist($lAge);

        // ---------------- FLUSH FINAL ----------------
        $em->flush();
    }
}
