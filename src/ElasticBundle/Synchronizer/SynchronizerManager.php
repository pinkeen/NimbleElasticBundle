<?php

namespace Nimble\ElasticBundle\Synchronizer;

use Nimble\ElasticBundle\Transformer\TransformerInterface;
use Nimble\ElasticBundle\Type\Type;

class SynchronizerManager
{
    /**
     * @var array
     */
    protected $synchronizers = [];

    /**
     * {@inheritdoc}
     */
    public function registerEntitySynchronizer(Synchronizer $synchronizer)
    {
        $this->synchronizers[$synchronizer->getClassName()][] = $synchronizer;
    }

    /**
     * @param object $entity
     * @return Synchronizer[]
     */
    protected function getSynchronizers($entity)
    {
        $className = get_class($entity);

        if (!isset($this->synchronizers[$className])) {
            return [];
        }

        return $this->synchronizers[$className];
    }

    /**
     * @param object $entity
     */
    public function synchronizeCreate($entity)
    {
        foreach ($this->getSynchronizers($entity) as $synchronizer) {
            $synchronizer->synchronizeCreate($entity);
        }
    }

    /**
     * @param object $entity
     */
    public function synchronizeUpdate($entity)
    {
        foreach ($this->getSynchronizers($entity) as $synchronizer) {
            $synchronizer->synchronizeUpdate($entity);
        }
    }

    /**
     * @param object $entity
     */
    public function synchronizeDelete($entity)
    {
        foreach ($this->getSynchronizers($entity) as $synchronizer) {
            $synchronizer->synchronizeDelete($entity);
        }
    }
}