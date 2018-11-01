<?php

namespace LAG\SmokerBundle\DependencyInjection\CompilerPass;

use LAG\SmokerBundle\Url\Provider\UrlProviderInterface;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class UrlProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(UrlProviderRegistry::class);

        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (null === $definition->getClass()) {
                continue;
            }

            if (!class_exists($definition->getClass())) {
                continue;
            }
            $implements = class_implements($definition->getClass());

            if (in_array(UrlProviderInterface::class, $implements)) {
                $registry->addMethodCall('add', [
                    new Reference($serviceId),
                ]);
            }
        }
    }
}
