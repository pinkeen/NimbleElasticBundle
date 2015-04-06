<?php

namespace Nimble\ElasticBundle\Transformer;

use Nimble\ElasticBundle\Document;

interface TransformerInterface
{
    /**
     * Transforms entity into elasticsearch document(s).
     *
     * Returns an elasticsearch document or array of them.
     *
     * This allows to denormaliza data.
     *
     * @param object $entity
     * @return Document[]
     */
    public function transformToDocuments($entity);

    /**
     * Transforms entity into elasticsearch id(s).
     *
     * Returns a single id or an array of them.
     *
     * This method is used during deletion where full transformation is not needed.
     *
     * @param object $entity
     * @return int[]|string[]
     */
    public function transformToIds($entity);

    /**
     * @return string
     */
    public function getClass();
}
