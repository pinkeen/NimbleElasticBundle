<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

class RegisterSynchronizersPass extends RegisterTaggedServiceWithManagerPass
{
    public function __construct()
    {
        parent::__construct(
            'nimble_elastic.synchronizer',
            'nimble_elastic.synchronizer_manager',
            'registerSynchronizer',
            'Nimble\ElasticBundle\Synchronizer\SynchronizerInterface'
        );
    }
}
