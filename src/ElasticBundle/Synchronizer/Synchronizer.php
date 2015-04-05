<?php

namespace Nimble\ElasticBundle\Synchronizer;

use Nimble\ElasticBundle\Transformer\TransformerInterface;
use Nimble\ElasticBundle\Type\Type;

class Synchronizer implements SynchronizerInterface
{
    /**
     * @var SynchronizedEntity[]
     */
    protected $synchronizations = [];

    /**
     * @var array
     */
    protected $registeredSynchronizations;

    /**
     * {@inheritdoc}
     */
    public function registerEntitySynchronization(
        $className,
        Type $type,
        TransformerInterface $transformer,
        $create = true,
        $update = true,
        $delete = true
    ) {
        $synchronized = new SynchronizedEntity($type, $transformer);

        if ($create) {
            $this->addSynchronization('create', $className, $synchronized);
        }

        if ($update) {
            $this->addSynchronization('update', $className, $synchronized);
        }

        if ($delete) {
            $this->addSynchronization('delete', $className, $synchronized);
        }
    }

    protected function addSynchronization($action, $className, SynchronizedEntity $synchronization)
    {
        $typeName = $synchronization->getType()->getName();
        $indexName = $synchronization->getType()->getIndex()->getName();

        if (isset($this->registeredSynchronizations[$action][$className][$indexName][$typeName])) {
            throw new EntitySynchronizationAlreadyRegisteredException($action, $className, $indexName, $typeName);
        }

        $this->registeredSynchronizations[$action][$className][$indexName][$typeName] = true;
        $this->synchronizations[$action][$className][] = $synchronization;
    }

    /**
     * @param string $action
     * @param object $entity
     * @return SynchronizedEntity[]
     */
    protected function getSynchronizations($action, $entity)
    {
        $className = get_class($entity);

        if (!isset($this->synchronizations[$action][$className])) {
            return null;
        }

        return $this->synchronizations[$action][$className];
    }

    /**
     * @param SynchronizedEntity[] $synchronizations
     * @param object $entity
     */
    protected function performPutSynchronizations(array $synchronizations, $entity)
    {
        foreach ($synchronizations as $synchronization) {
            $documents = $synchronization->getTransformer()->transformToDocument($entity);

            if (!is_array($documents)) {
                $documents = [$documents];
            }

            $synchronization->getType()->putDocuments(
                $documents
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($entity)
    {
        if (!$synchronizations = $this->getSynchronizations('update', $entity)) {
            return;
        }

        $this->performPutSynchronizations($synchronizations, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function create($entity)
    {
        if (!$synchronizations = $this->getSynchronizations('create', $entity)) {
            return;
        }

        $this->performPutSynchronizations($synchronizations, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity)
    {
        if (!$synchronizations = $this->getSynchronizations('delete', $entity)) {
            return;
        }

        foreach ($synchronizations as $synchronization) {
            $ids = $synchronization->getTransformer()->transformToId($entity);

            if (!is_array($ids)) {
                $ids = [$ids];
            }

            $synchronization->getType()->deleteDocuments(
                $ids
            );
        }
    }

    /**
     * Flushes any scheduled actions (if applicable).
     */
    public function flush()
    {
        // No flushing in the base synchronizer.
    }
}