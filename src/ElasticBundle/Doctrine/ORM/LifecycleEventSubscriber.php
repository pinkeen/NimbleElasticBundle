<?php

namespace Nimble\ElasticBundle\Doctrine\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Nimble\ElasticBundle\Synchronizer\SynchronizerInterface;

class LifecycleEventSubscriber implements EventSubscriber
{
    /**
     * @var SynchronizerInterface
     */
    private $synchronizer;

    /**
     * @param SynchronizerInterface $synchronizer
     */
    public function __construct(SynchronizerInterface $synchronizer)
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
        $this->synchronizer->update($args->getEntity());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->synchronizer->create($args->getEntity());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->synchronizer->delete($args->getEntity());
    }
}