<?php

namespace Nimble\ElasticBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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

        $this->processClients($config['default_client'], $config['clients'], $container);
        $this->processIndexes($config['indexes'], $container);
        $this->processListeners($config['synchronization_listeners'], $container);
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
            $clientDefinition->setClass('Elasticsearch\Client');
            $clientDefinition->setArguments([
                $clientConfig['hosts'],
                $clientConfig['logging']['enabled'] ? new Reference($clientConfig['logging']['service']) : null,
            ]);

            $clientDefinition->addTag('monolog.logger', ['channel' => 'elasticsearch']);

            $container->setDefinition($clientServiceId, $clientDefinition);
        }

        $container->setAlias('nimble_elastic.client', sprintf('nimble_elastic.client.%s', $defaultClient));
    }

    /**
     * @param array $typesConfig
     * @return array
     */
    protected function buildTypesSettings(array $typesConfig)
    {
        $types = [];

        foreach ($typesConfig as $typeName => $typeConfig) {
            $typeMappings = $typeConfig['mappings'];
            $types[$typeName] = [];

            if (!empty($typeMappings)) {
                $types[$typeName]['mappings'] = $typeMappings;
            }
        }

        return $types;
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
                $this->buildTypesSettings($typesConfig)
            ]);

            $indexDefinition->addTag('nimble_elastic.index');

            $container->setDefinition($indexServiceId, $indexDefinition);

            $this->processTypes($typesConfig, $indexName, $indexServiceId, $container);
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
        $populatorManagerServiceDefinition = $container->getDefinition('nimble_elastic.populator_manager');

        foreach ($typesConfig as $typeName => $typeConfig) {
            $typeServiceId = sprintf('nimble_elastic.type.%s.%s', $indexName, $typeName);

            $typeServiceDefinition = new Definition('Nimble\ElasticBundle\Type\Type', [$typeName]);
            $typeServiceDefinition->setFactory([new Reference($indexServiceId), 'getType']);

            $container->setDefinition($typeServiceId, $typeServiceDefinition);

            $this->processEntities($typeConfig['entities'], $indexName, $typeServiceId, $typeName, $container);

            /* TODO: Break this into function. Allow to register fetchers via tags. */
            if (isset($typeConfig['fetcher'])) {
                if (count($typeConfig['fetcher']) > 1) {
                    throw new InvalidConfigurationException(sprintf('Type "%s.%s" must have only one fetcher defined.'));
                }

                $fetcherServiceId = null;

                if (isset($typeConfig['fetcher']['service'])) {
                    $fetcherServiceId = $typeConfig['fetcher']['service'];
                } elseif (isset($typeConfig['fetcher']['doctrine_orm_entity'])) {
                    $fetcherServiceId = sprintf("nimble.elastic.fetcher.%s.%s", $indexName, $typeName);

                    $fetcherServiceDefinition = new Definition(
                        'Nimble\ElasticBundle\Doctrine\ORM\Populator\DoctrineORMPopulationFetcher',
                        [
                            new Reference('doctrine.orm.entity_manager'),
                            $typeConfig['fetcher']['doctrine_orm_entity']
                        ]
                    );

                    $container->setDefinition($fetcherServiceId, $fetcherServiceDefinition);
                }

                if ($fetcherServiceId) {
                    $populatorManagerServiceDefinition->addMethodCall('registerFetcher', [
                        new Reference($fetcherServiceId),
                        $indexName,
                        $typeName
                    ]);
                }
            }
        }
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
        $transformerManagerDefinition = $container->getDefinition('nimble_elastic.transformer_manager');

        foreach ($entitiesConfig as $entityClass => $entityConfig) {
            $synchronizerServiceId = sprintf('nimble_elastic.synchronizer.%s.%s.%s',
                $indexName,
                $typeName,
                $container->camelize($entityClass)
            );

            $synchronizerDefinition = new Definition('Nimble\ElasticBundle\Synchronizer\Synchronizer', [
                $entityClass,
                new Reference($typeServiceId),
                $entityConfig['on_create'],
                $entityConfig['on_update'],
                $entityConfig['on_delete'],
                new Reference('nimble_elastic.transformer_manager')
            ]);

            $synchronizerDefinition->addTag('nimble_elastic.synchronizer');
            $container->setDefinition($synchronizerServiceId, $synchronizerDefinition);

            /* Transformer service is optional because it can be registered via tags. */
            if (null !== $entityConfig['transformer_service']) {
                $transformerManagerDefinition->addMethodCall('registerTransformer', [
                    new Reference($entityConfig['transformer_service']),
                    $indexName,
                    $typeName
                ]);
            }
        }
    }

    /**
     * @param array $listeners
     * @param ContainerBuilder $container
     */
    protected function processListeners(array $listeners, ContainerBuilder $container)
    {
        /* Enables doctrine orm event subscriber by tagging it. */
        if ($listeners['doctrine_orm']['enabled']) {
            $listenerDefinition = $container->getDefinition('nimble_elastic.doctrine.orm.listener');
            $listenerDefinition->addTag('doctrine.event_subscriber', [
                'connection' => $listeners['doctrine_orm']['connection'],
            ]);
        }
    }
}
