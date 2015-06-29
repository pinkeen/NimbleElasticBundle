<?php

namespace Nimble\ElasticBundle\Client\Connection;

use Elasticsearch\Connections\GuzzleConnection;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class GuzzleDebugConnection extends GuzzleConnection
{
    /**
     * {@inheritdoc}
     */
    public function __construct($hostDetails, $connectionParams, LoggerInterface $log, LoggerInterface $trace)
    {
        parent::__construct($hostDetails, $connectionParams, $log, $trace);

        /** @var Client $client */
        $client = $connectionParams['guzzleClient'];

    }
}