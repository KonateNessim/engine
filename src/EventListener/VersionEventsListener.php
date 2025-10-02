<?php

namespace App\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use App\Service\{CompiledVersionCache, SnapshotManager};
use App\Entity\{VersionMethod, MethodLine, Place, ConditionGroup, Condition};

class VersionEventsListener
{
  public function __construct(private CompiledVersionCache $cache, private SnapshotManager $snap) {}
  public function postPersist(PostPersistEventArgs $args): void
  {
    $this->handle($args->getObject());
  }
  public function postUpdate(PostUpdateEventArgs $args): void
  {
    $this->handle($args->getObject());
  }
  private function handle(object $e): void
  {
    if ($e instanceof MethodLine || $e instanceof Place || $e instanceof ConditionGroup || $e instanceof Condition) {
      $line = $e instanceof MethodLine ? $e : ($e->getLine() ?? $e->getGroup()->getLine());
      if ($line) {
        $this->snap->createSnapshot($line);
        $vm = $line->getVersionMethod();
        if ($vm) $this->cache->invalidate($vm->getMethod()->getId(), $vm->getVersionNumber());
      }
    } elseif ($e instanceof VersionMethod) {
      $this->cache->invalidate($e->getMethod()->getId(), $e->getVersionNumber());
    }
  }
}
