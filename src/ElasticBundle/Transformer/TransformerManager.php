<?php

namespace Nimble\ElasticBundle\Transformer;

use Nimble\ElasticBundle\ClassUtils;
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
     * @param string $indexName
     * @param string $typeName
     */
    public function registerTransformer(TransformerInterface $transformer, $indexName, $typeName)
    {
        $className = $transformer->getClass();

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

        $classKey = ClassUtils::findClassKey($className, $this->transfomers);

        if (!$classKey) {
            throw new TransformerNotFoundException($className, $indexName, $typeName);
        }

        return $this->transfomers[$classKey][$indexName][$typeName];
    }

    /**
     * @param object $entity
     * @param string $indexName
     * @param string $typeName
     * @return Document[]
     */
    public function transformToDocuments($entity, $indexName, $typeName)
    {
        return $this->getTransformer($entity, $indexName, $typeName)->transformToDocuments($entity);
    }

    /**
     * @param object $entity
     * @param string $indexName
     * @param string $typeName
     * @return string[]|int[]
     */
    public function transformToIds($entity, $indexName, $typeName)
    {
        return $this->getTransformer($entity, $indexName, $typeName)->transformToIds($entity);
    }
}
