<?php

namespace App\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use App\Service\{CompiledVersionCache, SnapshotManager};
use App\Entity\{Method, MethodLine, Place, ConditionGroup, Condition};

/**
 * Listener Doctrine pour gérer les snapshots et le cache du moteur.
 * 
 * ⚙️ Fonctionnement :
 * - À chaque création ou mise à jour d'une ligne (ou ses sous-éléments),
 *   un snapshot est automatiquement créé.
 * - Le cache compilé associé à la méthode est invalidé.
 */
class VersionEventsListener
{
  public function __construct(
    private CompiledVersionCache $cache,
    private SnapshotManager $snap
  ) {}

  public function postPersist(PostPersistEventArgs $args): void
  {
    $this->handle($args->getObject());
  }

  public function postUpdate(PostUpdateEventArgs $args): void
  {
    $this->handle($args->getObject());
  }

  private function handle(object $entity): void
  {

    if (
      $entity instanceof MethodLine ||
      $entity instanceof Place ||
      $entity instanceof ConditionGroup ||
      $entity instanceof Condition
    ) {

      $line = $entity instanceof MethodLine
        ? $entity
        : ($entity->getLine() ?? $entity->getGroupCondition()?->getLine());

      if ($line) {
        $this->snap->createSnapshot($line);
        $method = $line->getMethod();
        if ($method instanceof Method) {
          $this->cache->invalidate($method->getId());
        }
      }
    }

    elseif ($entity instanceof Method) {
      $this->cache->invalidate($entity->getId());
    }
  }
}
