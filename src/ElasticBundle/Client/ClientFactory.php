<?php

namespace Nimble\ElasticBundle\Client;

use Elasticsearch\Client;

class ClientFactory
{
    /**
     * @param array $params
     * @return Client
     */
    public function createClient(array $configuration)
    {
        $params = [
            'hosts' => $configuration['hosts']
        ];

        return new Client($params);
    }
}
