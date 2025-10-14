<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;

use App\Entity\DataType;
use App\Entity\ItemType;
use App\Entity\EngineBranch;
use App\Entity\Method;
use App\Entity\MethodLine;
use App\Entity\Argument;
use App\Entity\Place;
use App\Entity\ConditionGroup;
use App\Entity\Condition;
use App\Entity\MethodRequirement;
use App\Enum\OperatorType;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $em): void
    {
        // ---------------- DATATYPES ----------------
        $dtInt = new DataType();
        $dtInt->setName('integer');
        $dtInt->setPhpType('int');
        $dtInt->setJsType('number');
        $em->persist($dtInt);

        $dtFloat = new DataType();
        $dtFloat->setName('float');
        $dtFloat->setPhpType('float');
        $dtFloat->setJsType('number');
        $em->persist($dtFloat);

        $dtStr = new DataType();
        $dtStr->setName('string');
        $dtStr->setPhpType('string');
        $dtStr->setJsType('text');
        $em->persist($dtStr);

        $dtDate = new DataType();
        $dtDate->setName('date');
        $dtDate->setPhpType('\DateTimeInterface');
        $dtDate->setJsType('date');
        $em->persist($dtDate);

        // ---------------- ITEMTYPES ----------------
        $itNumber = new ItemType();
        $itNumber->setName('number');
        $itNumber->setLabel('Champ numérique');
        $itNumber->setInputType('number');
        $em->persist($itNumber);

        $itInput = new ItemType();
        $itInput->setName('input');
        $itInput->setLabel('Texte');
        $itInput->setInputType('text');
        $em->persist($itInput);

        $itSelect = new ItemType();
        $itSelect->setName('select');
        $itSelect->setLabel('Choix');
        $itSelect->setInputType('select');
        $itSelect->setOptions(['particulier', 'professionnel']);
        $em->persist($itSelect);

        $itDate = new ItemType();
        $itDate->setName('date');
        $itDate->setLabel('Date');
        $itDate->setInputType('date');
        $em->persist($itDate);

        // ============================================================
        // ENGINE AUTO (objet vehicule + assure)
        // ============================================================
        $engineAuto = new EngineBranch();
        $engineAuto->setName('Moteur Auto avec Objet Vehicule/Assure');
        $engineAuto->setBranch(1);
        $engineAuto->setIsActive(true);
        $engineAuto->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineAuto);

        $methodAuto = new Method();
        $methodAuto->setEngine($engineAuto);
        $methodAuto->setInsurer(10);
        $methodAuto->setName('Calcul Prime Auto Objet');
        $methodAuto->setCode('CALC_PRIME_AUTO_OBJ');
        $methodAuto->setReturnType('float');
        $methodAuto->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodAuto);

        // Arguments vehicule.*
        $VEHICULE_PUIS = new Argument();
        $VEHICULE_PUIS->setName('vehicule.puissance');
        $VEHICULE_PUIS->setLabel('Puissance du véhicule');
        $VEHICULE_PUIS->setDataType($dtInt);
        $VEHICULE_PUIS->setItemType($itNumber);
        $VEHICULE_PUIS->setIsRequired(true);
        $VEHICULE_PUIS->setConstraints(['min' => 50, 'max' => 400]);
        $em->persist($VEHICULE_PUIS);

        $VEHICULE_USAGE = new Argument();
        $VEHICULE_USAGE->setName('vehicule.usage');
        $VEHICULE_USAGE->setLabel('Usage du véhicule');
        $VEHICULE_USAGE->setDataType($dtStr);
        $VEHICULE_USAGE->setItemType($itSelect);
        $VEHICULE_USAGE->setIsRequired(true);
        $VEHICULE_USAGE->setDefaultValue('particulier');
        $VEHICULE_USAGE->setConstraints(['enum' => ['particulier', 'professionnel']]);
        $em->persist($VEHICULE_USAGE);

        $VEHICULE_ANNEE = new Argument();
        $VEHICULE_ANNEE->setName('vehicule.anneeMiseEnCirculation');
        $VEHICULE_ANNEE->setLabel('Année mise en circulation');
        $VEHICULE_ANNEE->setDataType($dtInt);
        $VEHICULE_ANNEE->setItemType($itNumber);
        $VEHICULE_ANNEE->setIsRequired(true);
        $em->persist($VEHICULE_ANNEE);

        // Arguments assure.*
        $ASSURE_AGE = new Argument();
        $ASSURE_AGE->setName('assure.age');
        $ASSURE_AGE->setLabel('Âge de l’assuré');
        $ASSURE_AGE->setDataType($dtInt);
        $ASSURE_AGE->setItemType($itNumber);
        $ASSURE_AGE->setIsRequired(true);
        $ASSURE_AGE->setConstraints(['min' => 18, 'max' => 80]);
        $em->persist($ASSURE_AGE);

        $ASSURE_PROF = new Argument();
        $ASSURE_PROF->setName('assure.profession');
        $ASSURE_PROF->setLabel('Profession');
        $ASSURE_PROF->setDataType($dtStr);
        $ASSURE_PROF->setItemType($itSelect);
        $ASSURE_PROF->setIsRequired(false);
        $ASSURE_PROF->setDefaultValue('employé');
        $ASSURE_PROF->setConstraints(['enum' => ['cadre', 'artisan', 'employé']]);
        $em->persist($ASSURE_PROF);

        $ASSURE_DATE_NAISS = new Argument();
        $ASSURE_DATE_NAISS->setName('assure.dateNaissance');
        $ASSURE_DATE_NAISS->setLabel('Date de naissance');
        $ASSURE_DATE_NAISS->setDataType($dtDate);
        $ASSURE_DATE_NAISS->setItemType($itDate);
        $ASSURE_DATE_NAISS->setIsRequired(true);
        $em->persist($ASSURE_DATE_NAISS);

        // Requirements (ManyToOne vers Argument)
        $reqPuiss = new MethodRequirement();
        $reqPuiss->setMethod($methodAuto);
        $reqPuiss->setLabel('Puissance véhicule');
        $reqPuiss->setCode($VEHICULE_PUIS);
        $reqPuiss->setIsRequired(true);
        $reqPuiss->setValidationRules(['min' => 50, 'max' => 400]);
        $em->persist($reqPuiss);

        $reqAgeAuto = new MethodRequirement();
        $reqAgeAuto->setMethod($methodAuto);
        $reqAgeAuto->setLabel('Âge assuré');
        $reqAgeAuto->setCode($ASSURE_AGE);
        $reqAgeAuto->setIsRequired(true);
        $reqAgeAuto->setValidationRules(['min' => 18, 'max' => 80]);
        $em->persist($reqAgeAuto);

        // Ligne 1 : prime_base = vehicule.puissance * 1.2
        $l1 = new MethodLine();
        $l1->setMethod($methodAuto);
        $l1->setOrderIndex(1);
        $l1->setResultVariable('prime_base');
        $l1->setExpression('vehicule.puissance * 1.2');
        $em->persist($l1);

        // Ligne 2 : prime_reduite = prime_base * 0.95 si usage=particulier ET age<30
        $l2 = new MethodLine();
        $l2->setMethod($methodAuto);
        $l2->setOrderIndex(2);
        $l2->setResultVariable('prime_reduite');
        $l2->setExpression('prime_base * 0.95');
        $em->persist($l2);

        $cg2 = new ConditionGroup();
        $cg2->setLine($l2);
        $cg2->setLogicOperator('AND');
        $cg2->setOrderIndex(1);
        $em->persist($cg2);

        $c21 = new Condition();
        $c21->setGroup($cg2);
        $c21->setLeftArgument($VEHICULE_USAGE);
        $c21->setOperator(OperatorType::Equal);
        $c21->setRightValue('particulier');
        $c21->setOrderIndex(1);
        $em->persist($c21);

        $c22 = new Condition();
        $c22->setGroup($cg2);
        $c22->setLeftArgument($ASSURE_AGE);
        $c22->setOperator(OperatorType::LessThan);
        $c22->setRightValue('30');
        $c22->setOrderIndex(2);
        $em->persist($c22);

        // ============================================================
        // ENGINE SANTE (assure + avenant)
        // ============================================================
        $engineSante = new EngineBranch();
        $engineSante->setName('Moteur Santé');
        $engineSante->setBranch(2);
        $engineSante->setIsActive(true);
        $engineSante->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineSante);

        $methodSante = new Method();
        $methodSante->setEngine($engineSante);
        $methodSante->setName('Calcul Prime Santé');
        $methodSante->setCode('CALC_PRIME_SANTE_OBJ');
        $methodSante->setReturnType('float');
        $methodSante->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodSante);

        $ASSURE_AGE_SANTE = new Argument();
        $ASSURE_AGE_SANTE->setName('assure.age');
        $ASSURE_AGE_SANTE->setLabel('Âge assuré');
        $ASSURE_AGE_SANTE->setDataType($dtInt);
        $ASSURE_AGE_SANTE->setItemType($itNumber);
        $ASSURE_AGE_SANTE->setIsRequired(true);
        $ASSURE_AGE_SANTE->setConstraints(['min' => 0, 'max' => 120]);
        $em->persist($ASSURE_AGE_SANTE);

        $AVENANT_TYPE = new Argument();
        $AVENANT_TYPE->setName('avenant.type');
        $AVENANT_TYPE->setLabel('Type avenant');
        $AVENANT_TYPE->setDataType($dtStr);
        $AVENANT_TYPE->setItemType($itSelect);
        $AVENANT_TYPE->setIsRequired(false);
        $AVENANT_TYPE->setDefaultValue('ajout');
        $AVENANT_TYPE->setConstraints(['enum' => ['ajout', 'modification', 'suppression']]);
        $em->persist($AVENANT_TYPE);

        $AVENANT_OBJET = new Argument();
        $AVENANT_OBJET->setName('avenant.objet');
        $AVENANT_OBJET->setLabel('Objet avenant');
        $AVENANT_OBJET->setDataType($dtStr);
        $AVENANT_OBJET->setItemType($itInput);
        $AVENANT_OBJET->setIsRequired(false);
        $em->persist($AVENANT_OBJET);

        $reqSanteAge = new MethodRequirement();
        $reqSanteAge->setMethod($methodSante);
        $reqSanteAge->setLabel('Âge assuré');
        $reqSanteAge->setCode($ASSURE_AGE_SANTE);
        $reqSanteAge->setIsRequired(true);
        $reqSanteAge->setValidationRules(['min' => 0, 'max' => 120]);
        $em->persist($reqSanteAge);

        // prime_sante = assure.age * 1.5 (si avenant.type == 'ajout')
        $lS1 = new MethodLine();
        $lS1->setMethod($methodSante);
        $lS1->setOrderIndex(1);
        $lS1->setResultVariable('prime_sante');
        $lS1->setExpression('assure.age * 1.5');
        $em->persist($lS1);

        $cgS1 = new ConditionGroup();
        $cgS1->setLine($lS1);
        $cgS1->setLogicOperator('AND');
        $cgS1->setOrderIndex(1);
        $em->persist($cgS1);

        $cS1 = new Condition();
        $cS1->setGroup($cgS1);
        $cS1->setLeftArgument($AVENANT_TYPE);
        $cS1->setOperator(OperatorType::Equal);
        $cS1->setRightValue('ajout');
        $cS1->setOrderIndex(1);
        $em->persist($cS1);

        // ============================================================
        // ENGINE VOYAGE (assure + voyage)
        // ============================================================
        $engineVoy = new EngineBranch();
        $engineVoy->setName('Moteur Voyage');
        $engineVoy->setBranch(3);
        $engineVoy->setIsActive(true);
        $engineVoy->setCreatedAt(new DateTimeImmutable());
        $em->persist($engineVoy);

        $methodVoy = new Method();
        $methodVoy->setEngine($engineVoy);
        $methodVoy->setInsurer(20);
        $methodVoy->setName('Calcul Prime Voyage Objet');
        $methodVoy->setCode('CALC_PRIME_VOYAGE_OBJ');
        $methodVoy->setReturnType('float');
        $methodVoy->setCreatedAt(new DateTimeImmutable());
        $em->persist($methodVoy);

        $DUREE = new Argument();
        $DUREE->setName('voyage.duree');
        $DUREE->setLabel('Durée du voyage (jours)');
        $DUREE->setDataType($dtInt);
        $DUREE->setItemType($itNumber);
        $DUREE->setIsRequired(true);
        $em->persist($DUREE);

        $DEST_R = new Argument();
        $DEST_R->setName('voyage.destinationRisque');
        $DEST_R->setLabel('Destination à risque');
        $DEST_R->setDataType($dtInt);
        $DEST_R->setItemType($itNumber);
        $DEST_R->setIsRequired(true);
        $em->persist($DEST_R);

        $ASSURE_AGE_VOY = new Argument();
        $ASSURE_AGE_VOY->setName('assure.age');
        $ASSURE_AGE_VOY->setLabel('Âge du voyageur');
        $ASSURE_AGE_VOY->setDataType($dtInt);
        $ASSURE_AGE_VOY->setItemType($itNumber);
        $ASSURE_AGE_VOY->setIsRequired(true);
        $em->persist($ASSURE_AGE_VOY);

        // prime_voyage = voyage.duree * 20
        $lV1 = new MethodLine();
        $lV1->setMethod($methodVoy);
        $lV1->setOrderIndex(1);
        $lV1->setResultVariable('prime_voyage');
        $lV1->setExpression('voyage.duree * 20');
        $em->persist($lV1);

        $cgV1 = new ConditionGroup();
        $cgV1->setLine($lV1);
        $cgV1->setLogicOperator('AND');
        $cgV1->setOrderIndex(1);
        $em->persist($cgV1);

        $cV1 = new Condition();
        $cV1->setGroup($cgV1);
        $cV1->setLeftArgument($DEST_R);
        $cV1->setOperator(OperatorType::Equal);
        $cV1->setRightValue('1');
        $cV1->setOrderIndex(1);
        $em->persist($cV1);

        // ---------------- FLUSH ----------------
        $em->flush();
    }
}
