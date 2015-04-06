<?php

namespace Nimble\ElasticBundle\DependencyInjection;

use Nimble\ElasticBundle\DependencyInjection\Compiler\RegisterIndexesPass;
use Nimble\ElasticBundle\DependencyInjection\Compiler\RegisterSynchronizerPass;
use Nimble\ElasticBundle\DependencyInjection\Compiler\RegisterTransformersPass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NimbleElasticExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        //echo json_encode($config, JSON_PRETTY_PRINT);

        $this->processClients($config['default_client'], $config['clients'], $container);
        $this->processIndexes($config['indexes'], $container);
        $this->processListeners($config['synchronization_listeners'], $container);

        $container->addCompilerPass(new RegisterIndexesPass());
        $container->addCompilerPass(new RegisterSynchronizerPass());
        $container->addCompilerPass(new RegisterTransformersPass());
    }

    /**
     * @param array $listeners
     */
    protected function processListeners(array $listeners, ContainerBuilder $container)
    {
        if ($listeners['doctrine_orm']['enabled']) {
            $listenerDefinition = $container->getDefinition('nimble_elastic.doctrine.orm.listener');
            $listenerDefinition->addTag('doctrine.event_subscriber', [
                'connection' => $listeners['doctrine_orm']['connection'],
            ]);
        }
    }

    /**
     * @param array $typesConfig
     * @return array
     */
    protected function buildMappingConfiguration(array $typesConfig)
    {
        $mappings = [];

        foreach ($typesConfig as $typeName => $typeConfig) {
            $typeMappings = $typeConfig['mappings'];

            if (!empty($typeMappings)) {
                $mappings[$typeName]['properties'] = $typeMappings;
            }
        }

        return $mappings;
    }

    /**
     * @param array $entitiesConfig
     * @param string $indexName
     * @param string $typeServiceId
     * @param string $typeName
     * @param ContainerBuilder $container
     */
    protected function processEntities(array $entitiesConfig, $indexName, $typeServiceId, $typeName, ContainerBuilder $container)
    {
        foreach ($entitiesConfig as $entityClass => $entityConfig) {
            $synchronizerServiceId = sprintf('nimble_elastic.synchronizer.%s.%s.%s',
                $indexName,
                $typeName,
                $container->camelize($entityClass)
            );

            $synchronizerDefinition = new DefinitionDecorator('nimble_elastic.synchronizer.prototype');

            $synchronizerDefinition->replaceArgument(0, $entityClass);
            $synchronizerDefinition->replaceArgument(1, new Reference($typeServiceId));
            $synchronizerDefinition->replaceArgument(2, $entityConfig['on_create']);
            $synchronizerDefinition->replaceArgument(3, $entityConfig['on_update']);
            $synchronizerDefinition->replaceArgument(4, $entityConfig['on_delete']);

            $container->setDefinition($synchronizerServiceId, $synchronizerDefinition);

            $transformerDefinition = $container->getDefinition($entityConfig['transformer_service']);
            $transformerDefinition->addTag('nimble_elastic.transformer', [
                'index' => $indexName,
                'type' => $typeName
            ]);
        }
    }

    /**
     * @param array $typesConfig
     * @param string $indexName
     * @param string $indexServiceId
     * @param ContainerBuilder $container
     */
    protected function processTypes(array $typesConfig, $indexName, $indexServiceId, ContainerBuilder $container)
    {
        foreach ($typesConfig as $typeName => $typeConfig) {
            $typeServiceId = sprintf('%s.%s', $indexServiceId, $typeName);

            $typeServiceDefinition = new Definition('Nimble\ElasticBundle\Type\Type', [$typeName]);
            $typeServiceDefinition->setFactory([new Reference($indexServiceId), 'getType']);

            $container->setDefinition($typeServiceId, $typeServiceDefinition);

            $this->processEntities($typeConfig['entities'], $indexName, $typeServiceId, $typeName, $container);
        }
    }

    /**
     * @param array $indexesConfig
     * @param ContainerBuilder $container
     */
    protected function processIndexes(array $indexesConfig, ContainerBuilder $container)
    {
        foreach ($indexesConfig as $indexName => $indexConfig) {
            $indexServiceId = sprintf('nimble_elastic.index.%s', $indexName);
            $clientServiceId = 'nimble_elastic.client';

            $typesConfig = $indexConfig['types'];

            if (null !== $indexConfig['client']) {
                $clientServiceId = sprintf('nimble_elastic.client.%s', $indexConfig['client']);
            }

            $indexDefinition = new Definition('Nimble\ElasticBundle\Index\Index', [
                $indexName,
                new Reference($clientServiceId),
                $indexConfig['settings'],
                $this->buildMappingConfiguration($typesConfig)
            ]);

            $indexDefinition->addTag('nimble_elastic.index');

            $container->setDefinition($indexServiceId, $indexDefinition);

            $this->processTypes($typesConfig, $indexName, $indexServiceId, $container);
        }
    }

    /**
     * @param string $defaultClient
     * @param array $clientsConfig
     * @param ContainerBuilder $container
     */
    protected function processClients($defaultClient, array $clientsConfig, ContainerBuilder $container)
    {
        if (!isset($clientsConfig[$defaultClient])) {
            throw new InvalidConfigurationException(
                sprintf('Default client "%s" must be configured in "nimble_elastic.clients".', $defaultClient)
            );
        }

        foreach ($clientsConfig as $clientName => $clientConfig) {
            $clientServiceId = sprintf('nimble_elastic.client.%s', $clientName);

            $clientDefinition = new DefinitionDecorator('nimble_elastic.client_prototype');
            $clientDefinition->replaceArgument(0, $clientConfig);

            $container->setDefinition($clientServiceId, $clientDefinition);
        }

        $container->setAlias('nimble_elastic.client', sprintf('nimble_elastic.client.%s', $defaultClient));
    }
}
