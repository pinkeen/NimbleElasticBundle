<?php

namespace Nimble\ElasticBundle\Populator\Exception;

class PopulationFetcherNotFoundException extends \RuntimeException
{
    /**
     * @param string $indexName
     * @param string $typeName
     */
    public function __construct($indexName, $typeName)
    {
        parent::__construct(
            sprintf('No population fetcher was found for type "%s.%s"',
                $indexName,
                $typeName
            )
        );
    }
}