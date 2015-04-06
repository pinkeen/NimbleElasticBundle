<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterSynchronizersPass extends AbstractCompilerPass
{
    static protected $tagName = 'nimble_elastic.synchronizer';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $synchronizerManagerDefinition = $container->getDefinition('nimble_elastic.synchronizer_manager');

        foreach ($container->findTaggedServiceIds(self::$tagName) as $synchronizerServiceId => $tag) {
            $this->validateServiceClass(
                $container->getDefinition($synchronizerServiceId)->getClass(),
                'Nimble\ElasticBundle\Synchronizer\SynchronizerInterface',
                $synchronizerServiceId,
                self::$tagName
            );

            $synchronizerManagerDefinition->addMethodCall('registerSynchronizer', [
                new Reference($synchronizerServiceId)
            ]);
        }
    }
}
