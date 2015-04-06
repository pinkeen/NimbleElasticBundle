<?php

namespace Nimble\ElasticBundle\Transformer\Exception;

class TransformerNotFoundException extends \RuntimeException
{
    /**
     * @param string $className
     * @param string $indexName
     * @param string $typeName
     */
    public function __construct($className, $indexName, $typeName)
    {
        parent::__construct(
            sprintf('No transformer was found for class "%s" in type "%s.%s".',
                $className,
                $indexName,
                $typeName
            )
        );
    }
}
