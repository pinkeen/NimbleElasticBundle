<?php

namespace Nimble\ElasticBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
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
                                                    ->booleanNode('create')
                                                        ->defaultTrue()
                                                    ->end()
                                                    ->booleanNode('update')
                                                        ->defaultTrue()
                                                    ->end()
                                                    ->booleanNode('delete')
                                                        ->defaultTrue()
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
