<?php

namespace Nimble\ElasticBundle\Populator\Exception;

class PopulationFetcherNotFoundException extends \RuntimeException
{
    /**
     * @param string $indexId
     * @param string $typeName
     */
    public function __construct($indexId, $typeName)
    {
        parent::__construct(
            sprintf('No population fetcher was found for type "%s.%s"',
                $indexId,
                $typeName
            )
        );
    }
}