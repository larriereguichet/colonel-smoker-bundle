<?php

namespace LAG\SmokerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LAGSmokerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config/services')
        );
        $loader->load('collectors.yaml');
        $loader->load('commands.yaml');
        $loader->load('handlers.yaml');
        $loader->load('providers.yaml');
        $loader->load('registries.yaml');
        $loader->load('resolvers.yaml');

        $container->setParameter('lag_smoker.mapping', $config['mapping']);
        $container->setParameter('lag_smoker.routes', $config['routes']);
        $container->setParameter('lag_smoker.routing', $config['routing']);
    }

    public function getAlias()
    {
        return 'lag_smoker';
    }
}
