<?php

namespace LAG\SmokerBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BaseTestCase extends TestCase
{
    /**
     * Assert that the given service class is configured in the services.yaml
     *
     * @param string $serviceClass
     */
    public function assertServiceExists(string $serviceClass)
    {
        $containerBuilder = new ContainerBuilder();
        $locator = new FileLocator([
            __DIR__.'/../../src/Resources/config',
        ]);
        $loader = new YamlFileLoader($containerBuilder, $locator);
        $loader->load('services.yaml');
        $exists = false;

        foreach ($containerBuilder->getDefinitions() as $definition) {
            if ($serviceClass === $definition->getClass()) {
                $exists = true;
            }
        }

        $this->assertTrue($exists);
    }
}
