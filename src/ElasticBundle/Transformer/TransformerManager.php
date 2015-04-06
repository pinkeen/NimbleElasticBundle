<?php

namespace Nimble\ElasticBundle\Transformer;

use Nimble\ElasticBundle\Document;
use Nimble\ElasticBundle\Transformer\Exception\TransformerAlreadyRegisteredException;
use Nimble\ElasticBundle\Transformer\Exception\TransformerNotFoundException;

class TransformerManager
{
    /**
     * @var array
     */
    protected $transfomers = [];

    /**
     * @param TransformerInterface $transformer
     * @param string $className
     * @param string $indexName
     * @param string $typeName
     */
    public function registerTransformer(TransformerInterface $transformer, $className, $indexName, $typeName)
    {
        if (isset($this->transfomers[$className][$indexName][$typeName])) {
            throw new TransformerAlreadyRegisteredException($className, $indexName, $typeName);
        }

        $this->transfomers[$className][$indexName][$typeName] = $transformer;
    }

    /**
     * @param object $entity
     * @param string $indexName
     * @param string $typeName
     * @return TransformerInterface
     */
    protected function getTransformer($entity, $indexName, $typeName)
    {
        $className = get_class($entity);

        if (!isset($this->transfomers[$className][$indexName][$typeName])) {
            throw new TransformerNotFoundException($className, $indexName, $typeName);
        }

        return $this->transfomers[$className][$indexName][$typeName];
    }

    /**
     * @param object $entity
     * @param string $indexName
     * @param string $typeName
     * @return Document[]
     */
    public function transformToDocuments($entity, $indexName, $typeName)
    {
        $documents = $this->getTransformer($entity, $indexName, $typeName)->transformToDocument($entity);

        if (!is_array($documents)) {
            return [$documents];
        }

        return $documents;
    }

    /**
     * @param object $entity
     * @param string $indexName
     * @param string $typeName
     * @return string[]|int[]
     */
    public function transformToIds($entity, $indexName, $typeName)
    {
        $ids = $this->getTransformer($entity, $indexName, $typeName)->transformToId($entity);

        if (!is_array($ids)) {
            return [$ids];
        }

        return $ids;
    }
}