<?php

namespace Nimble\ElasticBundle\Transformer\Exception;

class TransformerAlreadyRegisteredException extends \RuntimeException
{
    /**
     * @param string $className
     * @param string $indexName
     * @param string $typeName
     */
    public function __construct($className, $indexName, $typeName)
    {
        parent::__construct(
            sprintf('A transformer is already registred for class "%s" in type "%s.%s".',
                $className,
                $indexName,
                $typeName
            )
        );
    }
}
