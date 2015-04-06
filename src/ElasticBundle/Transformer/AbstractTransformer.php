<?php

namespace Nimble\ElasticBundle\Transformer;

use Nimble\ElasticBundle\Document;
use Nimble\ElasticBundle\Exception\UnexpectedTypeException;

abstract class AbstractTransformer implements TransformerInterface
{
    /**
     * @param object $entity
     */
    private function validateEntityClass($entity)
    {
        if (!is_a($entity, $this->getClass())) {
            throw new UnexpectedTypeException($entity, $this->getClass());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transformToDocuments($entity)
    {
        $this->validateEntityClass($entity);

        $documents = $this->handleTransformToDocuments($entity);

        if (!is_array($documents)) {
            return [$documents];
        }

        return $documents;
    }

    /**
     * {@inheritdoc}
     */
    public function transformToIds($entity)
    {
        $this->validateEntityClass($entity);

        $ids = $this->handleTransformToIds($entity);

        if (!is_array($ids)) {
            return [$ids];
        }

        return $ids;
    }

    /**
     * Implement this method returning either a Document or an array of Document objects.
     *
     * You do not need to check the class of $entity as this is already handled.
     *
     * @param object $entity
     * @return Document[]|Document
     */
    abstract protected function handleTransformToDocuments($entity);

    /**
     * Implement this method returning either a single id or an array of ids.
     *
     * You do not need to check the class of $entity as this is already handled.
     *
     * @param object $entity
     * @return string|int|string[]|int[]
     */
    abstract protected function handleTransformToIds($entity);
}
