<?php

namespace Nimble\ElasticBundle\Transformer\Exception;

class TransformerNotFoundException extends \RuntimeException
{
    /**
     * @param string $className
     * @param string $indexId
     * @param string $typeName
     */
    public function __construct($className, $indexId, $typeName)
    {
        parent::__construct(
            sprintf('No transformer was found for class "%s" in type "%s.%s".',
                $className,
                $indexId,
                $typeName
            )
        );
    }
}
