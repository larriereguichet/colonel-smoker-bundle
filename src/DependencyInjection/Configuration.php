<?php

namespace LAG\SmokerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('lag_smoke');

        $root
            ->children()
                ->arrayNode('routing')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('scheme')
                            ->defaultValue('http')
                        ->end()
                        ->scalarNode('host')
                            ->defaultValue('127.0.0.1')
                        ->end()
                        ->scalarNode('port')
                            ->defaultValue('8000')
                        ->end()
                        ->scalarNode('base_url')
                            ->defaultValue('')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('routes')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('mapping')->end()
                            ->arrayNode('handlers')
                                ->defaultValue([
                                    'response_code' => '200',
                                ])
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('mapping')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('entity')->end()
                            ->scalarNode('pattern')->end()
                            ->scalarNode('route')->end()
                            ->scalarNode('provider')->defaultValue('doctrine')->end()
                            ->arrayNode('options')
                                ->variablePrototype()->end()
                            ->end()
                            ->arrayNode('requirements')
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('excludes')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
