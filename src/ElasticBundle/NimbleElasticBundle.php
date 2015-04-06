<?php

namespace Nimble\ElasticBundle;

use Nimble\ElasticBundle\DependencyInjection\Compiler\RegisterIndexesPass;
use Nimble\ElasticBundle\DependencyInjection\Compiler\RegisterSynchronizersPass;
use Nimble\ElasticBundle\DependencyInjection\Compiler\RegisterTransformersPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NimbleElasticBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterIndexesPass());
        $container->addCompilerPass(new RegisterSynchronizersPass());
        $container->addCompilerPass(new RegisterTransformersPass());
    }
}
