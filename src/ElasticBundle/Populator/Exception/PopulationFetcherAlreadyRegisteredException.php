<?php

namespace Nimble\ElasticBundle\Populator\Exception;

class PopulationFetcherAlreadyRegisteredException extends \RuntimeException
{
    /**
     * @param string $indexName
     * @param string $typeName
     */
    public function __construct($indexName, $typeName)
    {
        parent::__construct(
            sprintf('A fetcher is already registered for type "%s.%s".',
                $indexName,
                $typeName
            )
        );
    }
}