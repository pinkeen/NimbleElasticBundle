<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterTransformersPass implements  CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $transformerManagerDefinition = $container->getDefinition('nimble_elastic.transformer_manager');

        foreach ($container->findTaggedServiceIds('nimble_elastic.transformer') as $transformerServiceId => $tag) {
            $transformerManagerDefinition->addMethodCall('registerTransformer', [
                new Reference($transformerServiceId)
            ]);
        }
    }
}
