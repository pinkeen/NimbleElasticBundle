<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

class RegisterIndexesPass extends RegisterTaggedServiceWithManagerPass
{
    function __construct()
    {
        parent::__construct(
            'nimble_elastic.index',
            'nimble_elastic.index_manager',
            'registerIndex',
            'Nimble\ElasticBundle\Index\Index'
        );
    }
}
