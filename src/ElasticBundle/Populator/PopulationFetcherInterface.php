<?php

namespace Nimble\ElasticBundle\Populator;

interface PopulationFetcherInterface 
{
    /**
     * @return int
     */
    public function getEntityCount();

    /**
     * @param int $offset
     * @param int $limit
     * @return object[]
     */
    public function fetchEntities($offset = 0, $limit = null);
}