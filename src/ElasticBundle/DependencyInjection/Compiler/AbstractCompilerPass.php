<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Abstract compiler pass with convenience methods.
 */
abstract class AbstractCompilerPass implements CompilerPassInterface
{
    /**
     * @param string $serviceId
     * @param $actualClassName
     * @param string $expectedClassName
     * @param string $tagName
     */
    protected function validateServiceClass($actualClassName, $expectedClassName, $serviceId, $tagName)
    {
        if (!is_a($actualClassName, $expectedClassName, true)) {
            throw new \RuntimeException(sprintf('Expected service "%s" tagged with "%s" to be an instance of "%s" but instead got "%s".',
                $serviceId,
                $tagName,
                $expectedClassName,
                $actualClassName
            ));
        }
    }

    /**
     * @param array $actualAttributes
     * @param array $expectedAttributeNames
     * @param string $serviceId
     * @param string $tagName
     */
    protected function validateTagAttributes(array $actualAttributes, array $expectedAttributeNames, $serviceId, $tagName)
    {
        foreach ($expectedAttributeNames as $attrName) {
            if (!array_key_exists($attrName, $actualAttributes)) {
                throw new \RuntimeException(sprintf('Tag "%s" on service "%s" is missing attribute "%s".',
                    $tagName,
                    $serviceId,
                    $attrName
                ));
            }
        }
    }
}
