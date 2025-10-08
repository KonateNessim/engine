<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\{Method, MethodLine, Place, ConditionGroup, Condition};

class CompiledVersionCache
{
    public function __construct(
        private CacheInterface $cache,
        private VersionCompiler $compiler,
        private EntityManagerInterface $em
    ) {}

    public function get(int $methodId): array
    {
        $key = sprintf('method_%d_compiled', $methodId);

        return $this->cache->get($key, function (ItemInterface $item) use ($methodId) {
            $item->expiresAfter(3600);

            $method = $this->em->find(Method::class, $methodId);
            if (!$method) {
                throw new \RuntimeException("Méthode introuvable (ID $methodId)");
            }

            return $this->compiler->compile($method);
        });
    }

    public function invalidate(int $methodId): void
    {
        $this->cache->delete(sprintf('method_%d_compiled', $methodId));
    }
}
