<?php

namespace Nimble\ElasticBundle\Transformer\Exception;

class TransformerAlreadyRegisteredException extends \RuntimeException
{
    /**
     * @param string $className
     * @param string $indexId
     * @param string $typeName
     */
    public function __construct($className, $indexId, $typeName)
    {
        parent::__construct(
            sprintf('A transformer is already registred for class "%s" in type "%s.%s".',
                $className,
                $indexId,
                $typeName
            )
        );
    }
}
