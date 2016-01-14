<?php

namespace Nimble\ElasticBundle\Populator\Exception;

class PopulationFetcherAlreadyRegisteredException extends \RuntimeException
{
    /**
     * @param string $indexId
     * @param string $typeName
     */
    public function __construct($indexId, $typeName)
    {
        parent::__construct(
            sprintf('A fetcher is already registered for type "%s.%s".',
                $indexId,
                $typeName
            )
        );
    }
}