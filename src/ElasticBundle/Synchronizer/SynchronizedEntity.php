<?php

namespace Nimble\ElasticBundle\Synchronizer;

use Nimble\ElasticBundle\Index\Index;
use Nimble\ElasticBundle\Transformer\TransformerInterface;
use Nimble\ElasticBundle\Type\Type;

class SynchronizedEntity
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * @param Type $type
     * @param TransformerInterface $transformer
     */
    public function __construct(Type $type, TransformerInterface $transformer)
    {
        $this->type = $type;
        $this->transformer = $transformer;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }
}