<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

class RegisterIndexesPass extends RegisterTaggedServiceWithManagerPass
{
    public function __construct()
    {
        parent::__construct(
            'nimble_elastic.index',
            'Nimble\ElasticBundle\Index\IndexManager',
            'registerIndex',
            'Nimble\ElasticBundle\Index\Index'
        );
    }
}
