<?php

namespace Nimble\ElasticBundle\Synchronizer;

use Nimble\ElasticBundle\ClassUtils;

class SynchronizerManager
{
    /**
     * @var array
     */
    protected $synchronizers = [];

    /**
     * {@inheritdoc}
     */
    public function registerSynchronizer(SynchronizerInterface $synchronizer)
    {
        $this->synchronizers[$synchronizer->getClassName()][] = $synchronizer;
    }

    /**
     * @param object $entity
     * @return SynchronizerInterface[]
     */
    protected function getSynchronizers($entity)
    {
        $classKey = ClassUtils::findClassKey(get_class($entity), $this->synchronizers);

        if (!$classKey) {
            return [];
        }

        return $this->synchronizers[$classKey];
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
