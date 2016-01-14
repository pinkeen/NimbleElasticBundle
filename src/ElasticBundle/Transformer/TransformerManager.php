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
     * @param string $indexId
     * @param string $typeName
     */
    public function registerTransformer(TransformerInterface $transformer, $indexId, $typeName)
    {
        $className = $transformer->getClass();

        if (isset($this->transfomers[$className][$indexId][$typeName])) {
            throw new TransformerAlreadyRegisteredException($className, $indexId, $typeName);
        }

        $this->transfomers[$className][$indexId][$typeName] = $transformer;
    }

    /**
     * @param object $entity
     * @param string $indexId
     * @param string $typeName
     * @return TransformerInterface
     */
    protected function getTransformer($entity, $indexId, $typeName)
    {
        $className = get_class($entity);

        $classKey = ClassUtils::findClassKey($className, $this->transfomers);

        if (!$classKey) {
            throw new TransformerNotFoundException($className, $indexId, $typeName);
        }

        return $this->transfomers[$classKey][$indexId][$typeName];
    }

    /**
     * @param object $entity
     * @param string $indexId
     * @param string $typeName
     * @return Document[]
     */
    public function transformToDocuments($entity, $indexId, $typeName)
    {
        return $this->getTransformer($entity, $indexId, $typeName)->transformToDocuments($entity);
    }

    /**
     * @param object $entity
     * @param string $indexId
     * @param string $typeName
     * @return string[]|int[]
     */
    public function transformToIds($entity, $indexId, $typeName)
    {
        return $this->getTransformer($entity, $indexId, $typeName)->transformToIds($entity);
    }
}
