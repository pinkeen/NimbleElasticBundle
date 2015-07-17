<?php

namespace Nimble\ElasticBundle\Doctrine\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nimble\ElasticBundle\Synchronizer\SynchronizerManager;

class LifecycleEventSubscriber implements EventSubscriber
{
    const ACTION_UPDATE = "UPDATE";
    const ACTION_CREATE = "CREATE";
    const ACTION_DELETE = "DELETE";

    /**
     * @var array
     */
    protected $buffer = [];

    /**
     * @var array
     */
    protected $identifiers = [];

    /**
     * @var SynchronizerManager
     */
    private $synchronizer;

    /**
     * @param SynchronizerManager $synchronizer
     */
    public function __construct(SynchronizerManager $synchronizer)
    {
        $this->synchronizer = $synchronizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postUpdate,
            Events::preRemove,
            Events::postPersist,
            Events::postFlush
        ];
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        /* Restore identifiers of entities being removed, because doctrine clears them.
         * This may have unintended consequences.
         * See http://www.doctrine-project.org/jira/browse/DDC-1680
         * and https://groups.google.com/forum/#!topic/doctrine-user/bTukzq0QrSE (Pavel Horal's msg) */
        foreach ($this->buffer as $event) {
            $this->restoreEntityIdentifier($event['entity']);
        }

        $this->identifiers = [];

        foreach ($this->buffer as $event) {
            switch ($event['action']) {
                case self::ACTION_CREATE:
                    $this->synchronizer->synchronizeCreate($event['entity']);
                    break;

                case self::ACTION_UPDATE:
                    $this->synchronizer->synchronizeUpdate($event['entity']);
                    break;

                case self::ACTION_DELETE:
                    $this->synchronizer->synchronizeDelete($event['entity']);
                    break;
            }
        }

        $this->buffer = [];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->buffer[] = [
            'action' => self::ACTION_UPDATE,
            'entity' => $args->getEntity()
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->buffer[] = [
            'action' => self::ACTION_CREATE,
            'entity' => $args->getEntity()
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $this->storeEntityIdentifier($args);

        $this->buffer[] = [
            'action' => self::ACTION_DELETE,
            'entity' => $args->getEntity(),
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     * @return array
     */
    protected function storeEntityIdentifier(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        $this->identifiers[spl_object_hash($entity)] = [
            $em->getUnitOfWork()->getEntityIdentifier($entity),
            $em->getClassMetadata(get_class($entity))
        ];
    }

    /**
     * @param object $entity
     */
    protected function restoreEntityIdentifier($entity)
    {
        $hash = spl_object_hash($entity);

        if (!isset($this->identifiers[$hash])) {
            return;
        }

        /**
         * @var ClassMetadata $class
         * @var array $identifier
         */
        list($identifier, $class) = $this->identifiers[$hash];

        $class->setIdentifierValues($entity, $identifier);
    }
}
