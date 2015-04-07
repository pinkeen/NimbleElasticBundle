<?php

namespace Nimble\ElasticBundle\Exception;

class IndexNotFoundException extends \RuntimeException
{
    /**
     * @param string $indexName
     */
    public function __construct($indexName)
    {
        parent::__construct(sprintf('Index "%s" not found.', $indexName));
    }
}
