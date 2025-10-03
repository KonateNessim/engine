<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;

use App\Entity\{
    EngineBranch, Method, VersionMethod,
    MethodLine, Argument, Place, ConditionGroup, Condition,
    ItemType, DataType
};
use App\Enum\{OperatorType};

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
        $engineAuto->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineAuto);

        $methodAuto = new Method();
        $methodAuto->setEngine($engineAuto);
        $methodAuto->setInsurer(10);
        $methodAuto->setName('Calcul Prime Auto');
        $methodAuto->setCode('CALC_PRIME_AUTO');
        $methodAuto->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodAuto);

        $vAuto = new VersionMethod();
        $vAuto->setMethod($methodAuto);
        $vAuto->setVersionNumber('v1');
        $vAuto->setIsActive(true);
        $em->persist($vAuto);

        // Arguments AUTO
        $argsAuto = [];
        foreach (["VAR1","VAR2","VAR3","AGE","LOYALTY_YEARS"] as $n) {
            $a = new Argument();
            $a->setName($n);
            $a->setType('integer');
            $a->setIsRequired(true);
            $a->setDefaultValue(null);
            $em->persist($a);
            $argsAuto[$n] = $a;
        }

        // Ligne Auto : prime_base = (VAR1 + VAR2) / VAR3
        $l1 = new MethodLine();
        $l1->setVersionMethod($vAuto);
        $l1->setOrderIndex(1);
        $l1->setResultVariable('prime_base');
        $em->persist($l1);

        $p1 = new Place(); $p1->setLine($l1); $p1->setOperator(OperatorType::LParen); $p1->setOrderIndex(1); $em->persist($p1);
        $p2 = new Place(); $p2->setLine($l1); $p2->setArgument($argsAuto["VAR1"]); $p2->setOrderIndex(2); $em->persist($p2);
        $p3 = new Place(); $p3->setLine($l1); $p3->setOperator(OperatorType::Plus); $p3->setOrderIndex(3); $em->persist($p3);
        $p4 = new Place(); $p4->setLine($l1); $p4->setArgument($argsAuto["VAR2"]); $p4->setOrderIndex(4); $em->persist($p4);
        $p5 = new Place(); $p5->setLine($l1); $p5->setOperator(OperatorType::RParen); $p5->setOrderIndex(5); $em->persist($p5);
        $p6 = new Place(); $p6->setLine($l1); $p6->setOperator(OperatorType::Divide); $p6->setOrderIndex(6); $em->persist($p6);
        $p7 = new Place(); $p7->setLine($l1); $p7->setArgument($argsAuto["VAR3"]); $p7->setOrderIndex(7); $em->persist($p7);

        // Condition : AGE > 25
        $cg1 = new ConditionGroup(); $cg1->setLine($l1); $cg1->setLogicOperator('AND'); $cg1->setOrderIndex(1); $em->persist($cg1);
        $c1 = new Condition(); $c1->setGroup($cg1); $c1->setLeftArgument($argsAuto["AGE"]); $c1->setOperator(OperatorType::GreaterThan); $c1->setRightValue('25'); $c1->setOrderIndex(1); $em->persist($c1);

        // Ligne Auto 2 : prime_finale = prime_base * 0.9
        $l2 = new MethodLine();
        $l2->setVersionMethod($vAuto);
        $l2->setOrderIndex(2);
        $l2->setResultVariable('prime_finale');
        $l2->setExpression('prime_base * 0.9');
        $em->persist($l2);

        $cg2 = new ConditionGroup(); $cg2->setLine($l2); $cg2->setLogicOperator('AND'); $cg2->setOrderIndex(1); $em->persist($cg2);
        $c2 = new Condition(); $c2->setGroup($cg2); $c2->setLeftArgument($argsAuto["LOYALTY_YEARS"]); $c2->setOperator(OperatorType::GreaterOrEqual); $c2->setRightValue('5'); $c2->setOrderIndex(1); $em->persist($c2);

        // ================== ENGINE SANTE ==================
        $engineSante = new EngineBranch();
        $engineSante->setBranch(2);
        $engineSante->setName('Moteur Santé Individuelle');
        $engineSante->setIsActive(true);
        $engineSante->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineSante);

        $methodSante = new Method();
        $methodSante->setEngine($engineSante);
        $methodSante->setName('Calcul Prime Santé');
        $methodSante->setCode('CALC_PRIME_SANTE');
        $methodSante->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodSante);

        $vSante = new VersionMethod();
        $vSante->setMethod($methodSante);
        $vSante->setVersionNumber('v1');
        $vSante->setIsActive(true);
        $em->persist($vSante);

        $argsSante = [];
        foreach (["AGE_ASSURE","NB_ENFANTS","PATHOLOGIES"] as $n) {
            $a = new Argument();
            $a->setName($n);
            $a->setType('integer');
            $a->setIsRequired(false);
            $a->setDefaultValue(null);
            $em->persist($a);
            $argsSante[$n] = $a;
        }

        // Ligne Santé : prime_sante = (AGE_ASSURE * 1.2)
        $lS1 = new MethodLine();
        $lS1->setVersionMethod($vSante);
        $lS1->setOrderIndex(1);
        $lS1->setResultVariable('prime_sante');
        $em->persist($lS1);

        $pS1 = new Place(); $pS1->setLine($lS1); $pS1->setOperator(OperatorType::LParen); $pS1->setOrderIndex(1); $em->persist($pS1);
        $pS2 = new Place(); $pS2->setLine($lS1); $pS2->setArgument($argsSante["AGE_ASSURE"]); $pS2->setOrderIndex(2); $em->persist($pS2);
        $pS3 = new Place(); $pS3->setLine($lS1); $pS3->setOperator(OperatorType::Multiply); $pS3->setOrderIndex(3); $em->persist($pS3);
        $pS4 = new Place(); $pS4->setLine($lS1); $pS4->setLiteralValue("1.2"); $pS4->setOrderIndex(4); $em->persist($pS4);
        $pS5 = new Place(); $pS5->setLine($lS1); $pS5->setOperator(OperatorType::RParen); $pS5->setOrderIndex(5); $em->persist($pS5);

        // Condition : PATHOLOGIES > 0
        $cgS1 = new ConditionGroup(); $cgS1->setLine($lS1); $cgS1->setLogicOperator('AND'); $cgS1->setOrderIndex(1); $em->persist($cgS1);
        $cS1 = new Condition(); $cS1->setGroup($cgS1); $cS1->setLeftArgument($argsSante["PATHOLOGIES"]); $cS1->setOperator(OperatorType::GreaterThan); $cS1->setRightValue('0'); $cS1->setOrderIndex(1); $em->persist($cS1);

        // ================== ENGINE VOYAGE ==================
        $engineVoyage = new EngineBranch();
        $engineVoyage->setBranch(3);
        $engineVoyage->setName('Moteur Voyage International');
        $engineVoyage->setIsActive(true);
        $engineVoyage->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineVoyage);

        $methodVoyage = new Method();
        $methodVoyage->setEngine($engineVoyage);
        $methodVoyage->setInsurer(20);
        $methodVoyage->setName('Calcul Prime Voyage');
        $methodVoyage->setCode('CALC_PRIME_VOYAGE');
        $methodVoyage->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodVoyage);

        $vVoyage = new VersionMethod();
        $vVoyage->setMethod($methodVoyage);
        $vVoyage->setVersionNumber('v1');
        $vVoyage->setIsActive(true);
        $em->persist($vVoyage);

        $argsVoyage = [];
        foreach (["DUREE","DESTINATION_RISQUE","AGE_VOYAGEUR"] as $n) {
            $a = new Argument();
            $a->setName($n);
            $a->setType('integer');
            $a->setIsRequired(false);
            $a->setDefaultValue(null);
            $em->persist($a);
            $argsVoyage[$n] = $a;
        }

        // Ligne Voyage : prime_voyage = (DUREE * 15)
        $lV1 = new MethodLine();
        $lV1->setVersionMethod($vVoyage);
        $lV1->setOrderIndex(1);
        $lV1->setResultVariable('prime_voyage');
        $em->persist($lV1);

        $pV1 = new Place(); $pV1->setLine($lV1); $pV1->setOperator(OperatorType::LParen); $pV1->setOrderIndex(1); $em->persist($pV1);
        $pV2 = new Place(); $pV2->setLine($lV1); $pV2->setArgument($argsVoyage["DUREE"]); $pV2->setOrderIndex(2); $em->persist($pV2);
        $pV3 = new Place(); $pV3->setLine($lV1); $pV3->setOperator(OperatorType::Multiply); $pV3->setOrderIndex(3); $em->persist($pV3);
        $pV4 = new Place(); $pV4->setLine($lV1); $pV4->setLiteralValue("15"); $pV4->setOrderIndex(4); $em->persist($pV4);
        $pV5 = new Place(); $pV5->setLine($lV1); $pV5->setOperator(OperatorType::RParen); $pV5->setOrderIndex(5); $em->persist($pV5);

        // Condition : DESTINATION_RISQUE == 1
        $cgV1 = new ConditionGroup(); $cgV1->setLine($lV1); $cgV1->setLogicOperator('AND'); $cgV1->setOrderIndex(1); $em->persist($cgV1);
        $cV1 = new Condition(); $cV1->setGroup($cgV1); $cV1->setLeftArgument($argsVoyage["DESTINATION_RISQUE"]); $cV1->setOperator(OperatorType::Equal); $cV1->setRightValue('1'); $cV1->setOrderIndex(1); $em->persist($cV1);

        // ================== MÉTHODE GLOBALE CALCUL AGE ==================
        $methodAge = new Method();
        $methodAge->setName('Calcul Age');
        $methodAge->setCode('CALC_AGE');
        $methodAge->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodAge);

        $vAge = new VersionMethod();
        $vAge->setMethod($methodAge);
        $vAge->setVersionNumber('v1');
        $vAge->setIsActive(true);
        $em->persist($vAge);

        $argDateNaissance = new Argument();
        $argDateNaissance->setName("DATE_NAISSANCE");
        $argDateNaissance->setType("date");
        $argDateNaissance->setIsRequired(true);
        $argDateNaissance->setDefaultValue(null);
        $em->persist($argDateNaissance);

        $lAge = new MethodLine();
        $lAge->setVersionMethod($vAge);
        $lAge->setOrderIndex(1);
        $lAge->setResultVariable("AGE");
        // Ici, pas de places → directement une expression avec ExpressionLanguage
        $lAge->setExpression("(now().format('Y') - DATE_NAISSANCE.format('Y'))");
        $em->persist($lAge);

        // ---------------- FLUSH FINAL ----------------
        $em->flush();
    }
}
