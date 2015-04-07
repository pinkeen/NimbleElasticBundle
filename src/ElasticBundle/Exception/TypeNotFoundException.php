<?php

namespace Nimble\ElasticBundle\Exception;

class TypeNotFoundException extends \RuntimeException
{
    /**
     * @param string $typeName
     * @param string $indexName
     */
    public function __construct($typeName, $indexName = null)
    {
        if (null !== $indexName) {
            $message = sprintf('Type "%s.%s" not found.', $indexName, $typeName);
        } else {
            $message = sprintf('Type "%s" not found in any index.', $typeName);
        }

        parent::__construct($message);
    }
}
