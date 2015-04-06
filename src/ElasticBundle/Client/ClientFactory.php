<?php

namespace Nimble\ElasticBundle\Client;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

class ClientFactory
{
    /**
     * @param array $hosts
     * @param LoggerInterface $logger
     * @return Client
     */
    public function createClient(array $hosts, LoggerInterface $logger = null)
    {
        return new Client([
            'hosts' => $hosts,
            'logObject' => $logger,
            'logging' => null !== $logger,
        ]);
    }
}
