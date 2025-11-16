<?php

namespace App\Service;

use App\Entity\{MethodLine, MethodLineVersion, Place, ConditionGroup, Condition, Argument};
use Doctrine\ORM\EntityManagerInterface;

class SnapshotManager
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Crée un snapshot de la ligne (line + places + groups/conditions).
     */
    public function createSnapshot(MethodLine $line, ?string $versionName = null): MethodLineVersion
    {
        $last = $this->em->getRepository(MethodLineVersion::class)
            ->findOneBy(['line' => $line], ['versionNumber' => 'DESC']);
        $next = ($last?->getVersionNumber() ?? 0) + 1;

        // Génère automatiquement un nom de version si non fourni
        if ($versionName === null) {
            $versionName = $this->generateVersionName($next, $last?->getVersionName());
        }

        $places = $this->em->getRepository(Place::class)
            ->findBy(['line' => $line], ['orderIndex' => 'ASC']);
        $groups = $this->em->getRepository(ConditionGroup::class)
            ->findBy(['line' => $line], ['orderIndex' => 'ASC']);

        $groupJson = [];
        foreach ($groups as $g) {
            $conds = $this->em->getRepository(Condition::class)
                ->findBy(['groupCondition' => $g], ['orderIndex' => 'ASC']);
            $groupJson[] = [
                'logic' => $g->getLogicOperator(),
                'order' => $g->getOrderIndex(),
                'conditions' => array_map(fn($c) => [
                    'left'  => $c->getLeftArgument()?->getName(),
                    'op'    => $c->getOperator()->value,
                    'right' => $c->getRightValue(),
                    'order' => $c->getOrderIndex()
                ], $conds)
            ];
        }

        $snapshot = [
            'line' => [
                'orderIndex'     => $line->getOrderIndex(),
                'resultVariable' => $line->getResultVariable(),
                'lineType'       => $line->getLineType(),
                'expression'     => $line->getExpression(),
                'metadata'       => $line->getMetadata()
            ],
            'places' => array_map(fn($p) => [
                'order' => $p->getOrderIndex(),
                'op'    => $p->getOperator()?->value,
                'arg'   => $p->getArgument()?->getName(),
                'val'   => $p->getLiteralValue()
            ], $places),
            'groups' => $groupJson
        ];

        $ver = new MethodLineVersion();
        $ver->setLine($line);
        $ver->setVersionNumber($next);
        $ver->setVersionName($versionName);
        $ver->setSnapshotJson($snapshot);
        $ver->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($ver);
        $this->em->flush();

        return $ver;
    }

    /**
     * Génère automatiquement un nom de version
     */
    private function generateVersionName(int $versionNumber, ?string $lastVersionName = null): string
    {
        // Si c'est la première version
        if ($versionNumber === 1) {
            return 'v1.0';
        }

        // Si on a une version précédente, on l'incrémente
        if ($lastVersionName && preg_match('/^v(\d+)\.(\d+)$/', $lastVersionName, $matches)) {
            $major = (int)$matches[1];
            $minor = (int)$matches[2] + 1;
            return "v{$major}.{$minor}";
        }

        // Fallback : utilise le numéro de version
        return "v1.{$versionNumber}";
    }

    /**
     * Met à jour le nom d'une version existante
     */
    public function updateVersionName(MethodLineVersion $version, string $versionName): void
    {
        $version->setVersionName($versionName);
        $this->em->flush();
    }

    /**
     * Restaure une ligne depuis un snapshot donné.
     */
    public function restoreSnapshot(MethodLine $line, int $versionNumber): void
    {
        $ver = $this->em->getRepository(MethodLineVersion::class)
            ->findOneBy(['line' => $line, 'versionNumber' => $versionNumber]);
        if (!$ver) {
            throw new \RuntimeException("Version $versionNumber introuvable pour la ligne {$line->getId()}");
        }

        $snapshot = $ver->getSnapshotJson();

        $line->setOrderIndex($snapshot['line']['orderIndex']);
        $line->setResultVariable($snapshot['line']['resultVariable']);
        $line->setLineType($snapshot['line']['lineType']);
        $line->setExpression($snapshot['line']['expression']);
        $line->setMetadata($snapshot['line']['metadata']);

        foreach ($this->em->getRepository(Place::class)->findBy(['line' => $line]) as $p) {
            $this->em->remove($p);
        }
        foreach ($snapshot['places'] as $p) {
            $pl = new Place();
            $pl->setLine($line);
            $pl->setOrderIndex($p['order']);
            if ($p['op']) $pl->setOperator(\App\Enum\OperatorType::from($p['op']));
            if ($p['arg']) {
                $arg = $this->em->getRepository(Argument::class)->findOneBy(['name' => $p['arg']]);
                if ($arg) $pl->setArgument($arg);
            }
            if ($p['val']) $pl->setLiteralValue($p['val']);
            $this->em->persist($pl);
        }

        foreach ($this->em->getRepository(ConditionGroup::class)->findBy(['line' => $line]) as $g) {
            $this->em->remove($g);
        }
        foreach ($snapshot['groups'] as $g) {
            $gr = new ConditionGroup();
            $gr->setLine($line);
            $gr->setLogicOperator($g['logic']);
            $gr->setOrderIndex($g['order']);
            $this->em->persist($gr);
            foreach ($g['conditions'] as $c) {
                $co = new Condition();
                $co->setGroupCondition($gr);
                $co->setOrderIndex($c['order']);
                if ($c['left']) {
                    $arg = $this->em->getRepository(Argument::class)->findOneBy(['name' => $c['left']]);
                    if ($arg) $co->setLeftArgument($arg);
                }
                $co->setOperator(\App\Enum\OperatorType::from($c['op']));
                $co->setRightValue($c['right']);
                $this->em->persist($co);
            }
        }

        $this->em->flush();
    }
}