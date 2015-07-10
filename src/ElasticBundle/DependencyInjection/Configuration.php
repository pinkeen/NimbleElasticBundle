<?php

namespace Nimble\ElasticBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configures the bundle.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('nimble_elastic');

        $rootNode
            ->children()
                ->scalarNode('default_client')
                    ->defaultValue('default')
                ->end()
                ->arrayNode('clients')
                    ->isRequired()
                    ->performNoDeepMerging()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('hosts')
                                ->requiresAtLeastOneElement()
                                ->isRequired()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('logging')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('enabled')
                                        ->defaultFalse()
                                    ->end()
                                    ->scalarNode('service')
                                        ->defaultValue('logger')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('synchronization_listeners')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine_orm')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->scalarNode('connection')->defaultValue('default')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('indexes')
                    ->requiresAtLeastOneElement()
                    ->isRequired()
                    ->performNoDeepMerging()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('client')
                                ->defaultNull()
                            ->end()
                            ->variableNode('settings')
                                ->defaultValue([])
                            ->end()
                            ->arrayNode('types')
                                ->requiresAtLeastOneElement()
                                ->isRequired()
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('fetcher')
                                            ->children()
                                                ->scalarNode('service')->end()
                                                ->scalarNode('doctrine_orm_entity')->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('entities')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('transformer_service')
                                                        ->defaultNull()
                                                    ->end()
                                                    ->scalarNode('logger_service')
                                                        ->defaultNull()
                                                    ->end()
                                                    ->enumNode('on_create')
                                                        ->values(['create', 'update', 'delete', false])
                                                        ->defaultValue('create')
                                                    ->end()
                                                    ->enumNode('on_update')
                                                        ->values(['create', 'update', 'delete', false])
                                                        ->defaultValue('update')
                                                    ->end()
                                                    ->enumNode('on_delete')
                                                        ->values(['create', 'update', 'delete', false])
                                                        ->defaultValue('delete')
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->variableNode('mappings')
                                            ->defaultValue([])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
