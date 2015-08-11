<?php

namespace Nimble\ElasticBundle\Client;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
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
        $builder =  ClientBuilder::create();

        $builder
            ->setHosts($hosts)
        ;

        if (null !== $logger) {
            $builder->setLogger($logger);
        }

        return $builder->build();
    }
}
