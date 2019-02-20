<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterTransformersPass extends AbstractCompilerPass
{
    static protected $tagName = 'nimble_elastic.transformer';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $transformerManagerDefinition = $container->getDefinition('Nimble\ElasticBundle\Transformer\TransformerManager');

        foreach ($container->findTaggedServiceIds(self::$tagName) as $transformerServiceId => $tags) {
            foreach ($tags as $attributes) {
                $this->validateTagAttributes($attributes, ['index', 'type'], $transformerServiceId, self::$tagName);
                $this->validateServiceClass(
                    $container->getDefinition($transformerServiceId)->getClass(),
                    'Nimble\ElasticBundle\Transformer\TransformerInterface',
                    $transformerServiceId,
                    self::$tagName
                );

                $transformerManagerDefinition->addMethodCall('registerTransformer', [
                    new Reference($transformerServiceId),
                    $attributes['index'],
                    $attributes['type']
                ]);
            }
        }
    }
}
