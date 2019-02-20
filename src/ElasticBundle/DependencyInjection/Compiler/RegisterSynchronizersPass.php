<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

class RegisterSynchronizersPass extends RegisterTaggedServiceWithManagerPass
{
    public function __construct()
    {
        parent::__construct(
            'nimble_elastic.synchronizer',
            'Nimble\ElasticBundle\Synchronizer\SynchronizerManager',
            'registerSynchronizer',
            'Nimble\ElasticBundle\Synchronizer\SynchronizerInterface'
        );
    }
}
