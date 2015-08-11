<?php

namespace Nimble\ElasticBundle\Client;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Pinkeen\ApiDebugBundle\Bridge\RingPHP\DataCollectorMiddleware;
use Psr\Log\LoggerInterface;

class ClientFactory
{
    /**
     * @var DataCollectorMiddleware
     */
    private $middleware;

    public function __construct(DataCollectorMiddleware $middleware)
    {
        $this->middleware = $middleware;
    }


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

        $handler = $this->middleware->createHandler(ClientBuilder::defaultHandler(), 'elasticsearch');

        $builder->setHandler($handler);

        return $builder->build();
    }
}
