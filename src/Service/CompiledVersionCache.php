<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use App\Entity\VersionMethod;
use Doctrine\ORM\EntityManagerInterface;

class CompiledVersionCache
{
  public function __construct(private CacheInterface $cache, private VersionCompiler $compiler, private EntityManagerInterface $em) {}
  public function get(int $methodId, string $versionNumber = 'v1'): array
  {
    $key = sprintf('vm_%d_%s', $methodId, $versionNumber);
    return $this->cache->get($key, function (ItemInterface $item) use ($methodId, $versionNumber) {
      $item->expiresAfter(3600);
      $vm = $this->em->getRepository(VersionMethod::class)->findOneBy(['method' => $methodId, 'versionNumber' => $versionNumber, 'isActive' => true]);
      if (!$vm) throw new \RuntimeException('VersionMethod introuvable');
      return $this->compiler->compile($vm);
    });
  }
  public function invalidate(int $methodId, string $versionNumber = 'v1'): void
  {
    $this->cache->delete(sprintf('vm_%d_%s', $methodId, $versionNumber));
  }
}
