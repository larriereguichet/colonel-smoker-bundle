<?php

namespace LAG\SmokerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('jk_smoke');

        $root
            ->children()
                ->arrayNode('mapping')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('entity')->end()
                            ->scalarNode('pattern')->end()
                            ->scalarNode('route')->end()
                            ->arrayNode('excludes')
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('requirements')
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
