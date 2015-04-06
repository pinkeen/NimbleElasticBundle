<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\Tests\Fixtures\Reference;

class RegisterIndexesPass implements  CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $indexManagerDefinition = $container->getDefinition('nimble_elastic.index_manager');

        foreach ($container->findTaggedServiceIds('nimble_elastic.index') as $indexServiceId => $tag) {
            $indexManagerDefinition->addMethodCall('registerIndex', [
                new Reference($indexServiceId)
            ]);
        }
    }
}
