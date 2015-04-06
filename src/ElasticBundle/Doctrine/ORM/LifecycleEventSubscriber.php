<?php

namespace Nimble\ElasticBundle\Doctrine\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Nimble\ElasticBundle\Synchronizer\SynchronizerManager;

class LifecycleEventSubscriber implements EventSubscriber
{
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
            'postUpdate',
            'postRemove',
            'postPersist',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->synchronizer->synchronizeUpdate($args->getEntity());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->synchronizer->synchronizeCreate($args->getEntity());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->synchronizer->synchronizeDelete($args->getEntity());
    }
}