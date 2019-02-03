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
                ->scalarNode('host')
                    ->defaultValue('http://127.0.0.1:8000')
                ->end()
                ->arrayNode('routes')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('mapping')->end()
                            ->arrayNode('handlers')
                                ->defaultValue([
                                    'response_code' => '200',
                                ])
                                ->scalarPrototype()->end()
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
                            ->scalarNode('provider')->defaultValue('default')->end()
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
