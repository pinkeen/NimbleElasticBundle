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
                                ->prototype('scalar')->end()
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
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('entities')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('transformer_service')
                                                        ->isRequired()
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
