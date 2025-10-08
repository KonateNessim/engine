<?php

namespace App\Tests\Service;

use App\Entity\{Method, MethodRequirement, DataType, ItemType};
use App\Service\MethodInputValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Service\MethodInputValidator
 */
class MethodInputValidatorTest extends TestCase
{
    private EntityManagerInterface $em;
    private MethodInputValidator $validator;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->validator = new MethodInputValidator($this->em);
    }

    private function mockRequirement(
        string $code,
        string $label,
        string $type,
        bool $required = true,
        mixed $default = null,
        array $rules = []
    ): MethodRequirement {
        $req = new MethodRequirement();
        $req->setCode($code);
        $req->setLabel($label);

        $dt = new DataType();
        $dt->setName($type);
        $req->setDataType($dt);

        $it = new ItemType();
        $it->setName('input');
        $req->setItemType($it);

        $req->setIsRequired($required);
        $req->setDefaultValue($default);
        $req->setValidationRules($rules);
        $req->setMethod(new Method());

        return $req;
    }

    public function test_valid_inputs_pass_validation(): void
    {
        $method = new Method();

        $requirements = [
            $this->mockRequirement('AGE', 'Âge assuré', 'integer', true, null, ['min' => 18]),
            $this->mockRequirement('SEXE', 'Sexe', 'string', true, null, ['enum' => ['M', 'F']]),
        ];

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findBy')->willReturn($requirements);
        $this->em->method('getRepository')->willReturn($repo);

        $inputs = ['AGE' => 30, 'SEXE' => 'M'];
        $this->validator->validate($method, $inputs);

        $this->assertSame(30, $inputs['AGE']);
        $this->assertSame('M', $inputs['SEXE']);
    }

    public function test_missing_required_field_throws_exception(): void
    {
        $method = new Method();
        $requirements = [
            $this->mockRequirement('AGE', 'Âge assuré', 'integer', true),
        ];

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findBy')->willReturn($requirements);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Champ obligatoire manquant : AGE");

        $inputs = [];
        $this->validator->validate($method, $inputs);
    }

    public function test_type_mismatch_throws_exception(): void
    {
        $method = new Method();
        $requirements = [
            $this->mockRequirement('AGE', 'Âge assuré', 'integer', true),
        ];

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findBy')->willReturn($requirements);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Type invalide pour AGE");

        $inputs = ['AGE' => 'trente'];
        $this->validator->validate($method, $inputs);
    }

    public function test_value_below_min_rule_throws_exception(): void
    {
        $method = new Method();
        $requirements = [
            $this->mockRequirement('AGE', 'Âge assuré', 'integer', true, null, ['min' => 18]),
        ];

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findBy')->willReturn($requirements);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("La valeur de AGE doit être supérieure ou égale à 18");

        $inputs = ['AGE' => 10];
        $this->validator->validate($method, $inputs);
    }

    public function test_default_value_is_applied_when_not_provided(): void
    {
        $method = new Method();
        $requirements = [
            $this->mockRequirement('NB_ENFANTS', 'Nombre d’enfants', 'integer', false, 2),
        ];

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findBy')->willReturn($requirements);
        $this->em->method('getRepository')->willReturn($repo);

        $inputs = [];
        $this->validator->validate($method, $inputs);

        $this->assertSame(2, $inputs['NB_ENFANTS']);
    }
}
