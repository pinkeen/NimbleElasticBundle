<?php

namespace Nimble\ElasticBundle\Index\Exception;

class TypeNotFoundException extends \RuntimeException
{
    /**
     * @param string $typeName
     * @param string $indexName
     */
    public function __construct($typeName, $indexName)
    {
        parent::__construct(sprintf('Type "%s" not found in index "%s".', $typeName, $indexName));
    }
}
