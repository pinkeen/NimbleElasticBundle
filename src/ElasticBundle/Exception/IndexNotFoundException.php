<?php

namespace Nimble\ElasticBundle\Exception;

class IndexNotFoundException extends \RuntimeException
{
    /**
     * @param string $indexId
     */
    public function __construct($indexId)
    {
        parent::__construct(sprintf('Index "%s" not found.', $indexId));
    }
}
