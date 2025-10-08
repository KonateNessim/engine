<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\{MethodRequirement, Method, DataType, ItemType};

class MethodRequirementFixtures extends Fixture
{
    public function load(ObjectManager $em): void
    {
        $dataTypes = [];
        foreach ([
            'integer' => ['int', 'number'],
            'float'   => ['float', 'number'],
            'string'  => ['string', 'text'],
            'date'    => ['\DateTime', 'date'],
            'boolean' => ['bool', 'checkbox'],
        ] as $name => [$php, $js]) {
            $dt = new DataType();
            $dt->setName($name);
            $dt->setPhpType($php);
            $dt->setJsType($js);
            $dt->setConstraints([]);
            $em->persist($dt);
            $dataTypes[$name] = $dt;
        }

        $itemTypes = [];
        foreach ([
            ['text', 'Texte', 'text'],
            ['number', 'Nombre', 'number'],
            ['select', 'Sélecteur', 'select'],
            ['date', 'Date', 'date'],
            ['checkbox', 'Case à cocher', 'checkbox'],
        ] as [$name, $label, $input]) {
            $it = new ItemType();
            $it->setName($name);
            $it->setLabel($label);
            $it->setInputType($input);
            $it->setOptions([]);
            $it->setValidation([]);
            $em->persist($it);
            $itemTypes[$name] = $it;
        }

        $em->flush();

        // ==================== METHODES EXISTANTES ====================

        $methodAuto = $em->getRepository(Method::class)->findOneBy(['code' => 'CALC_PRIME_AUTO']);
        $methodSante = $em->getRepository(Method::class)->findOneBy(['code' => 'CALC_PRIME_SANTE']);
        $methodVoyage = $em->getRepository(Method::class)->findOneBy(['code' => 'CALC_PRIME_VOYAGE']);
        $methodAge = $em->getRepository(Method::class)->findOneBy(['code' => 'CALC_AGE']);

        // ==================== AUTO ====================
        if ($methodAuto) {
            $r1 = new MethodRequirement();
            $r1->setMethod($methodAuto);
            $r1->setLabel("Valeur 1");
            $r1->setCode("VAR1");
            $r1->setDataType($dataTypes['integer']);
            $r1->setItemType($itemTypes['number']);
            $r1->setIsRequired(true);
            $r1->setValidationRules(['min' => 0]);
            $em->persist($r1);

            $r2 = new MethodRequirement();
            $r2->setMethod($methodAuto);
            $r2->setLabel("Âge du conducteur");
            $r2->setCode("AGE");
            $r2->setDataType($dataTypes['integer']);
            $r2->setItemType($itemTypes['number']);
            $r2->setIsRequired(true);
            $r2->setValidationRules(['min' => 18, 'max' => 75]);
            $em->persist($r2);

            $r3 = new MethodRequirement();
            $r3->setMethod($methodAuto);
            $r3->setLabel("Années de fidélité");
            $r3->setCode("LOYALTY_YEARS");
            $r3->setDataType($dataTypes['integer']);
            $r3->setItemType($itemTypes['number']);
            $r3->setIsRequired(false);
            $r3->setDefaultValue(0);
            $em->persist($r3);
        }

        // ==================== SANTÉ ====================
        if ($methodSante) {
            $r4 = new MethodRequirement();
            $r4->setMethod($methodSante);
            $r4->setLabel("Âge de l’assuré");
            $r4->setCode("AGE_ASSURE");
            $r4->setDataType($dataTypes['integer']);
            $r4->setItemType($itemTypes['number']);
            $r4->setIsRequired(true);
            $r4->setValidationRules(['min' => 18, 'max' => 80]);
            $em->persist($r4);

            $r5 = new MethodRequirement();
            $r5->setMethod($methodSante);
            $r5->setLabel("Nombre d’enfants");
            $r5->setCode("NB_ENFANTS");
            $r5->setDataType($dataTypes['integer']);
            $r5->setItemType($itemTypes['number']);
            $r5->setIsRequired(false);
            $r5->setDefaultValue(0);
            $em->persist($r5);

            $r6 = new MethodRequirement();
            $r6->setMethod($methodSante);
            $r6->setLabel("Pathologies déclarées");
            $r6->setCode("PATHOLOGIES");
            $r6->setDataType($dataTypes['integer']);
            $r6->setIsRequired(true);
            $r6->setValidationRules(['min' => 0]);
            $em->persist($r6);
        }

        // ==================== VOYAGE ====================
        if ($methodVoyage) {
            $r7 = new MethodRequirement();
            $r7->setMethod($methodVoyage);
            $r7->setLabel("Durée du voyage");
            $r7->setCode("DUREE");
            $r7->setDataType($dataTypes['integer']);
            $r7->setIsRequired(true);
            $r7->setValidationRules(['min' => 1]);
            $em->persist($r7);

            $r8 = new MethodRequirement();
            $r8->setMethod($methodVoyage);
            $r8->setLabel("Destination à risque ?");
            $r8->setCode("DESTINATION_RISQUE");
            $r8->setDataType($dataTypes['boolean']);
            $r8->setIsRequired(false);
            $r8->setDefaultValue(false);
            $em->persist($r8);
        }

        // ==================== AGE ====================
        if ($methodAge) {
            $r9 = new MethodRequirement();
            $r9->setMethod($methodAge);
            $r9->setLabel("Date de naissance");
            $r9->setCode("DATE_NAISSANCE");
            $r9->setDataType($dataTypes['date']);
            $r9->setItemType($itemTypes['date']);
            $r9->setIsRequired(true);
            $em->persist($r9);
        }

        $em->flush();
    }
}
