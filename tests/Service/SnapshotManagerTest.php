<?php

namespace App\Tests\Service;

use App\Entity\{MethodLine, MethodLineVersion, Place, ConditionGroup, Condition};
use App\Service\SnapshotManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SnapshotManagerTest extends TestCase
{
    private EntityManagerInterface $em;
    private SnapshotManager $manager;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->manager = new SnapshotManager($this->em);
    }

    #[Test]
    public function it_creates_snapshot_with_all_associations(): void
    {
        $line = new MethodLine();
        $line->setOrderIndex(1);
        $line->setResultVariable('PRIME');
        $line->setExpression('VAR1 + VAR2');
        $line->setLineType('calculation');
        $line->setMetadata(['some meta']);

        // --- Mock des entités liées ---
        $place1 = new Place();
        $place1->setOrderIndex(1);
        $place1->setLiteralValue('10');
        $place1->setOperator(\App\Enum\OperatorType::Plus);

        $group1 = new ConditionGroup();
        $group1->setLogicOperator('AND');
        $group1->setOrderIndex(1);

        $cond1 = new Condition();
        $cond1->setOperator(\App\Enum\OperatorType::Equal);
        $cond1->setRightValue('X');

        // --- Mock des repositories ---
        $repoPlace = $this->createMock(EntityRepository::class);
        $repoPlace->method('findBy')->willReturn([$place1]);

        $repoGroup = $this->createMock(EntityRepository::class);
        $repoGroup->method('findBy')->willReturn([$group1]);

        $repoCond = $this->createMock(EntityRepository::class);
        $repoCond->method('findBy')->willReturn([$cond1]);

        $repoVer = $this->createMock(EntityRepository::class);
        $repoVer->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturnMap([
            [Place::class, $repoPlace],
            [ConditionGroup::class, $repoGroup],
            [Condition::class, $repoCond],
            [MethodLineVersion::class, $repoVer],
        ]);

        $this->em->expects($this->once())->method('persist');
        $snapshot = $this->manager->createSnapshot($line);

        $this->assertInstanceOf(MethodLineVersion::class, $snapshot);
        $this->assertEquals(1, $snapshot->getVersionNumber());
        $data = $snapshot->getSnapshotJson();

        $this->assertArrayHasKey('line', $data);
        $this->assertArrayHasKey('places', $data);
        $this->assertArrayHasKey('groups', $data);
        $this->assertEquals('PRIME', $data['line']['resultVariable']);
    }

    #[Test]
    public function it_increments_version_number_on_new_snapshot(): void
    {
        $line = new MethodLine();

        $lastVersion = new MethodLineVersion();
        $lastVersion->setLine($line);
        $lastVersion->setVersionNumber(5);

        $repoVer = $this->createMock(EntityRepository::class);
        $repoVer->method('findOneBy')->willReturn($lastVersion);

        $repoEmpty = $this->createMock(EntityRepository::class);
        $repoEmpty->method('findBy')->willReturn([]);

        $this->em->method('getRepository')->willReturnMap([
            [Place::class, $repoEmpty],
            [ConditionGroup::class, $repoEmpty],
            [Condition::class, $repoEmpty],
            [MethodLineVersion::class, $repoVer],
        ]);

        $this->em->expects($this->once())->method('persist');
        $snapshot = $this->manager->createSnapshot($line);

        $this->assertEquals(6, $snapshot->getVersionNumber(), 'Le numéro de version doit s’incrémenter.');
    }
}
