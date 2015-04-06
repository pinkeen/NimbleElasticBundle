<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\Tests\Fixtures\Reference;

class RegisterSynchronizerPass implements  CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $synchronizerManagerDefinition = $container->getDefinition('nimble_elastic.synchronizer_manager');

        foreach ($container->findTaggedServiceIds('nimble_elastic.synchronizer') as $synchronizerServiceId => $tag) {
            $synchronizerManagerDefinition->addMethodCall('registerSynchronizer', [
                new Reference($synchronizerServiceId)
            ]);
        }
    }
}
