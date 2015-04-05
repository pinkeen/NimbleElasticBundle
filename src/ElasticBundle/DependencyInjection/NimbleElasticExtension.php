<?php

namespace Nimble\ElasticBundle\DependencyInjection;

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

        echo json_encode($config, JSON_PRETTY_PRINT);

        $this->processClients($config['default_client'], $config['clients'], $container);
        $this->processIndexes($config['indexes'], $container);
        $this->processListeners($config['synchronization_listeners'], $container);
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
     * @param array $typesConfig
     * @param $indexServiceId
     * @param ContainerBuilder $container
     */
    protected function processTypes(array $typesConfig, $indexServiceId, ContainerBuilder $container)
    {
        foreach ($typesConfig as $typeName => $typeConfig) {
            $typeServiceId = sprintf('%s.%s', $indexServiceId, $typeName);

            $typeServiceDefinition = new Definition('Nimble\ElasticBundle\Type\Type', [$typeName]);
            $typeServiceDefinition->setFactory([new Reference($indexServiceId), 'getType']);

            $container->setDefinition($typeServiceId, $typeServiceDefinition);

            $synchronizerDefinition = $container->getDefinition('nimble_elastic.synchronizer');

            foreach ($typeConfig['entities'] as $entityClassName => $entityConfig) {
                $synchronizerDefinition->addMethodCall('registerEntitySynchronization', [
                    $entityClassName,
                    new Reference($typeServiceId),
                    new Reference($entityConfig['transformer_service']),
                    $entityConfig['create'],
                    $entityConfig['update'],
                    $entityConfig['delete'],
                ]);
            }
        }
    }

    /**
     * @param array $indexesConfig
     * @param ContainerBuilder $container
     */
    protected function processIndexes(array $indexesConfig, ContainerBuilder $container)
    {
        $indexManagerDefinition = $container->getDefinition('nimble_elastic.index_manager');

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

            $container->setDefinition($indexServiceId, $indexDefinition);

            $indexManagerDefinition->addMethodCall('registerIndex', [
                new Reference($indexServiceId)
            ]);

            $this->processTypes($typesConfig, $indexServiceId, $container);
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
