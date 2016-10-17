<?php

namespace Nimble\ElasticBundle\Client;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Pinkeen\ApiDebugBundle\Bridge\RingPHP\Service\RingPHPHandlerFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ClientFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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

        if ($this->container->has('ring_php.handler_factory')) {
            /** @var RingPHPHandlerFactory $handlerFactory */
            $handlerFactory = $this->container->get('ring_php.handler_factory');
            $builder->setHandler($handlerFactory->create(
                ClientBuilder::defaultHandler(),
                'elastic'
            ));
        }

        return $builder->build();
    }
}

