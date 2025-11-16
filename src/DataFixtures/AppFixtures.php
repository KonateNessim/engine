<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;

use App\Entity\{
    DataType,
    ItemType,
    EngineBranch,
    Method,
    MethodLine,
    Argument,
    Place,
    ConditionGroup,
    Condition,
    MethodRequirement
};
use App\Enum\OperatorType;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $em): void
    {
        // ============================================================
        // ----------------------- DATATYPES --------------------------
        // ============================================================
        $dtInt = new DataType();
        $dtInt->setName('integer');
        $dtInt->setPhpType('int');
        $dtInt->setJsType('number');

        $dtFloat = new DataType();
        $dtFloat->setName('float');
        $dtFloat->setPhpType('float');
        $dtFloat->setJsType('number');

        $dtStr = new DataType();
        $dtStr->setName('string');
        $dtStr->setPhpType('string');
        $dtStr->setJsType('text');


        $dtDate = new DataType();
        $dtDate->setName('date');
        $dtDate->setPhpType('\DateTimeInterface');
        $dtDate->setJsType('date');

        foreach ([$dtInt, $dtFloat, $dtStr, $dtDate] as $d) {
            $em->persist($d);
        }
        $em->flush();

        $itNumber = new ItemType();
        $itNumber->setName('number');
        $itNumber->setLabel('Champ numérique');
        $itNumber->setInputType('number');

        $itInput = new ItemType();
        $itInput->setName('input');
        $itInput->setLabel('Texte libre');
        $itInput->setInputType('text');

        $itSelect = new ItemType();
        $itSelect->setName('select');
        $itSelect->setLabel('Choix');
        $itSelect->setInputType('select');
        $itSelect->setOptions(['particulier', 'professionnel', 'entreprise']);

        $itDate = new ItemType();
        $itDate->setName('date');
        $itDate->setLabel('Date');
        $itDate->setInputType('date');
        foreach ([$itNumber, $itInput, $itSelect, $itDate] as $i) {
            $em->persist($i);
        }
        $em->flush();

        // ============================================================
        // ----------------------- ENGINE AUTO ------------------------
        // ============================================================
        $engineAuto = new EngineBranch();
        $engineAuto->setName('Moteur Auto');
        $engineAuto->setBranch(1);
        $engineAuto->setIsActive(true);
        $engineAuto->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineAuto);
        $em->flush();

        $methodAuto = new Method();
        $methodAuto->setEngine($engineAuto);
        $methodAuto->setInsurer(10);
        $methodAuto->setName('Calcul Prime Auto Objet');
        $methodAuto->setPublicName('Calcul de la prime automobile');
        $methodAuto->setCode('CALC_PRIME_AUTO_OBJ');
        $methodAuto->setReturnType('float');
        $methodAuto->setCategory('calculation');
        $methodAuto->setIsImmutable(false);
        $methodAuto->setDescription('Calcule la prime automobile en fonction de la puissance, de l’usage et de l’âge de l’assuré.');
        $methodAuto->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodAuto);
        $em->flush();

        // --- Arguments véhicule
        $vehiculePuissance = new Argument();
            $vehiculePuissance->setName('vehicule.puissance');
            $vehiculePuissance->setLabel('Puissance du véhicule');
            $vehiculePuissance->setDataType($dtInt);
            $vehiculePuissance->setItemType($itNumber);
            $vehiculePuissance->setIsRequired(true);
            $vehiculePuissance->setConstraints(['min' => 50, 'max' => 400]);
            $vehiculePuissance->setDefaultValue(110);
        $em->persist($vehiculePuissance);

        $vehiculeUsage = new Argument();
            $vehiculeUsage->setName('vehicule.usage');
            $vehiculeUsage->setLabel('Usage du véhicule');
            $vehiculeUsage->setDataType($dtStr);
            $vehiculeUsage->setItemType($itSelect);
            $vehiculeUsage->setIsRequired(true);
            $vehiculeUsage->setDefaultValue('particulier');
            $vehiculeUsage->setConstraints(['enum' => ['particulier', 'professionnel']]);
        $em->persist($vehiculeUsage);

        $vehiculeAnnee = new Argument();
            $vehiculeAnnee->setName('vehicule.anneeMiseEnCirculation');
            $vehiculeAnnee->setLabel('Année de mise en circulation');
            $vehiculeAnnee->setDataType($dtInt);
            $vehiculeAnnee->setItemType($itNumber);
            $vehiculeAnnee->setIsRequired(true);
            $vehiculeAnnee->setDefaultValue(2018);
        $em->persist($vehiculeAnnee);

        // --- Arguments assuré
        $assureAge = new Argument();
            $assureAge->setName('assure.age');
            $assureAge->setLabel('Âge de l’assuré');
            $assureAge->setDataType($dtInt);
            $assureAge->setItemType($itNumber);
            $assureAge->setIsRequired(true);
            $assureAge->setConstraints(['min' => 18, 'max' => 80]);
            $assureAge->setDefaultValue(30);
        $em->persist($assureAge);

        $assureProfession = new Argument();
            $assureProfession->setName('assure.profession');
            $assureProfession->setLabel('Profession');
            $assureProfession->setDataType($dtStr);
            $assureProfession->setItemType($itSelect);
            $assureProfession->setIsRequired(false);
            $assureProfession->setDefaultValue('employé');
            $assureProfession->setConstraints(['enum' => ['cadre', 'artisan', 'employé']]);
        $em->persist($assureProfession);

        $em->flush();

        // --- Requirements
        $reqPuissance = (new MethodRequirement())
            ->setMethod($methodAuto)
            ->setLabel('Puissance véhicule')
            ->setCode($vehiculePuissance)
            ->setIsRequired(true)
            ->setValidationRules(['min' => 50, 'max' => 400]);
        $em->persist($reqPuissance);

        $reqAge = (new MethodRequirement())
            ->setMethod($methodAuto)
            ->setLabel('Âge assuré')
            ->setCode($assureAge)
            ->setIsRequired(true)
            ->setValidationRules(['min' => 18, 'max' => 80]);
        $em->persist($reqAge);

        $em->flush();

        // --- Lignes de calcul
        $l1 = new MethodLine();
            $l1->setMethod($methodAuto);
            $l1->setOrderIndex(1);
            $l1->setResultVariable('prime_base');
            $l1->setExpression('vehicule.puissance * 1.2');
        $em->persist($l1);

        $l2 = new MethodLine();
            $l2->setMethod($methodAuto);
            $l2->setOrderIndex(2);
            $l2->setResultVariable('prime_reduite');
            $l2->setExpression('prime_base * 0.95');
        $em->persist($l2);
        $em->flush();

        $cg2 = new ConditionGroup();
            $cg2->setLine($l2);
            $cg2->setLogicOperator('AND');
            $cg2->setOrderIndex(1);
        $em->persist($cg2);
        $em->flush();

        $c21 = new Condition();
            $c21->setGroupCondition($cg2);
            $c21->setLeftArgument($vehiculeUsage);
            $c21->setOperator(OperatorType::Equal);
            $c21->setRightValue('particulier');
            $c21->setOrderIndex(1);
        $em->persist($c21);

        $c22 = new Condition();
            $c22->setGroupCondition($cg2);
            $c22->setLeftArgument($assureAge);
            $c22->setOperator(OperatorType::LessThan);
            $c22->setRightValue('30');
            $c22->setOrderIndex(2);
        $em->persist($c22);

        $em->flush();

        // ============================================================
        // ----------------------- ENGINE SANTÉ -----------------------
        // ============================================================
        $engineSante = new EngineBranch();
        $engineSante->setName('Moteur Santé');
        $engineSante->setBranch(2);
        $engineSante->setIsActive(true);
        $engineSante->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineSante);
        $em->flush();

        $methodSante = new Method();
        $methodSante->setEngine($engineSante);
        $methodSante->setName('Calcul Prime Santé');
        $methodSante->setPublicName('Calcul de la prime santé');
        $methodSante->setCode('CALC_PRIME_SANTE_OBJ');
        $methodSante->setReturnType('float');
        $methodSante->setCategory('calculation');
        $methodSante->setDescription('Calcule la prime santé selon l’âge et le type d’avenant.');
        $methodSante->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodSante);
        $em->flush();

        $assureAgeSante = new Argument();
            $assureAgeSante->setName('assure.age');
            $assureAgeSante->setLabel('Âge assuré');
            $assureAgeSante->setDataType($dtInt);
            $assureAgeSante->setItemType($itNumber);
            $assureAgeSante->setIsRequired(true);
            $assureAgeSante->setConstraints(['min' => 0, 'max' => 120]);
            $assureAgeSante->setDefaultValue(45);
        $em->persist($assureAgeSante);

        $avenantType = new Argument();
            $avenantType->setName('avenant.type');
            $avenantType->setLabel('Type d’avenant');
            $avenantType->setDataType($dtStr);
            $avenantType->setItemType($itSelect);
            $avenantType->setIsRequired(false);
            $avenantType->setDefaultValue('ajout');
            $avenantType->setConstraints(['enum' => ['ajout', 'modification', 'suppression']]);
        $em->persist($avenantType);
        $em->flush();

        $reqSanteAge = (new MethodRequirement())
            ->setMethod($methodSante)
            ->setLabel('Âge assuré')
            ->setCode($assureAgeSante)
            ->setIsRequired(true)
            ->setValidationRules(['min' => 0, 'max' => 120]);
        $em->persist($reqSanteAge);
        $em->flush();

        $lS1 = new MethodLine();
            $lS1->setMethod($methodSante);
            $lS1->setOrderIndex(1);
            $lS1->setResultVariable('prime_sante');
            $lS1->setExpression('assure.age * 1.5');
        $em->persist($lS1);
        $em->flush();

        $cgS1 = new ConditionGroup();
            $cgS1->setLine($lS1);
            $cgS1->setLogicOperator('AND');
            $cgS1->setOrderIndex(1);
        $em->persist($cgS1);
        $em->flush();

        $cS1 = new Condition();
            $cS1->setGroupCondition($cgS1);
            $cS1->setLeftArgument($avenantType);
            $cS1->setOperator(OperatorType::Equal);
            $cS1->setRightValue('ajout');
            $cS1->setOrderIndex(1);
        $em->persist($cS1);
        $em->flush();

        // ============================================================
        // ----------------------- ENGINE VOYAGE ----------------------
        // ============================================================
        $engineVoy = new EngineBranch();
        $engineVoy->setName('Moteur Voyage');
        $engineVoy->setBranch(3);
        $engineVoy->setIsActive(true);
        $engineVoy->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineVoy);
        $em->flush();

        $methodVoy = new Method();
        $methodVoy->setEngine($engineVoy);
        $methodVoy->setInsurer(20);
        $methodVoy->setName('Calcul Prime Voyage Objet');
        $methodVoy->setPublicName('Calcul de la prime voyage');
        $methodVoy->setCode('CALC_PRIME_VOYAGE_OBJ');
        $methodVoy->setReturnType('float');
        $methodVoy->setCategory('calculation');
        $methodVoy->setDescription('Calcule la prime de voyage selon la durée et le risque de destination.');
        $methodVoy->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodVoy);
        $em->flush();

        $voyDuree = new Argument();
        $voyDuree->setName('voyage.duree');
        $voyDuree->setLabel('Durée du voyage (jours)');
        $voyDuree->setDataType($dtInt);
        $voyDuree->setItemType($itNumber);
        $voyDuree->setIsRequired(true);
        $voyDuree->setDefaultValue(10);
        $em->persist($voyDuree);

        $voyDestRisque = new Argument();
        $voyDestRisque->setName('voyage.destinationRisque');
        $voyDestRisque->setLabel('Destination à risque');
        $voyDestRisque->setDataType($dtInt);
        $voyDestRisque->setItemType($itNumber);
        $voyDestRisque->setIsRequired(true);
        $voyDestRisque->setDefaultValue(0);
        $em->persist($voyDestRisque);
        $em->flush();

        $lV1 = new MethodLine();
        $lV1->setMethod($methodVoy);
        $lV1->setOrderIndex(1);
        $lV1->setResultVariable('prime_voyage');
        $lV1->setExpression('voyage.duree * 20');
        $em->persist($lV1);
        $em->flush();

        $cgV1 = new ConditionGroup();
        $cgV1->setLine($lV1);
        $cgV1->setLogicOperator('AND');
        $cgV1->setOrderIndex(1);
        $em->persist($cgV1);
        $em->flush();

        $cV1 = new Condition();
        $cV1->setGroupCondition($cgV1);
        $cV1->setLeftArgument($voyDestRisque);
        $cV1->setOperator(OperatorType::Equal);
        $cV1->setRightValue('1');
        $cV1->setOrderIndex(1);
        $em->persist($cV1);
        $em->flush();
    }
}
