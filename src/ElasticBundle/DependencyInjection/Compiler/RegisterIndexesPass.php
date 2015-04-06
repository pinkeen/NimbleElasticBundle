<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterIndexesPass extends AbstractCompilerPass
{
    static protected $tagName = 'nimble_elastic.index';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $indexManagerDefinition = $container->getDefinition('nimble_elastic.index_manager');

        foreach ($container->findTaggedServiceIds(self::$tagName) as $indexServiceId => $tag) {
            $this->validateServiceClass(
                $container->getDefinition($indexServiceId)->getClass(),
                'Nimble\ElasticBundle\Index\Index',
                $indexServiceId,
                self::$tagName
            );

            $indexManagerDefinition->addMethodCall('registerIndex', [
                new Reference($indexServiceId)
            ]);
        }
    }
}
