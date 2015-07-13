<?php

namespace Nimble\ElasticBundle\Doctrine\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
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
        $this->buffer[] = [
            'action' => self::ACTION_DELETE,
            'entity' => $args->getEntity()
        ];
    }
}
