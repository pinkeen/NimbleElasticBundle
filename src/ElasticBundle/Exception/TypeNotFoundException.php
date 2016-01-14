<?php

namespace Nimble\ElasticBundle\Exception;

class TypeNotFoundException extends \RuntimeException
{
    /**
     * @param string $typeName
     * @param string $indexId
     */
    public function __construct($typeName, $indexId = null)
    {
        if (null !== $indexId) {
            $message = sprintf('Type "%s.%s" not found.', $indexId, $typeName);
        } else {
            $message = sprintf('Type "%s" not found in any index.', $typeName);
        }

        parent::__construct($message);
    }
}
