<?php

namespace Nimble\ElasticBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterTaggedServiceWithManagerPass extends AbstractCompilerPass
{
    /**
     * @var string
     */
    private $tagName;

    /**
     * @var string
     */
    private $managerServiceId;

    /**
     * @var string
     */
    private $registrationMethodName;

    /**
     * @var string
     */
    private $registeredServiceClassName;

    /**
     * @param string $tagName
     * @param string $managerServiceId
     * @param string $registrationMethodName
     * @param string $registeredServiceClassName
     */
    public function __construct($tagName, $managerServiceId, $registrationMethodName, $registeredServiceClassName)
    {
        $this->tagName = $tagName;
        $this->managerServiceId = $managerServiceId;
        $this->registrationMethodName = $registrationMethodName;
        $this->registeredServiceClassName = $registeredServiceClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $synchronizerManagerDefinition = $container->getDefinition($this->managerServiceId);

        foreach ($container->findTaggedServiceIds($this->tagName) as $registeredServiceId => $tag) {
            $this->validateServiceClass(
                $container->getDefinition($registeredServiceId)->getClass(),
                $this->registeredServiceClassName,
                $this->managerServiceId,
                $this->tagName
            );

            $synchronizerManagerDefinition->addMethodCall($this->registrationMethodName, [
                new Reference($registeredServiceId)
            ]);
        }
    }
}
