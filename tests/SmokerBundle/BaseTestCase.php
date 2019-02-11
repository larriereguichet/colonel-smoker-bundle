<?php

namespace LAG\SmokerBundle\Tests;

use Closure;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BaseTestCase extends TestCase
{
    /**
     * @param string|string[] $originalClassName
     *
     * @return MockObject|mixed
     */
    protected function createMock($originalClassName): MockObject
    {
        return parent::createMock($originalClassName);
    }

    /**
     * Assert that the given service class is configured in the services.yaml
     *
     * @param string $serviceClass
     */
    protected function assertServiceExists(string $serviceClass)
    {
        $containerBuilder = new ContainerBuilder();
        $locator = new FileLocator([
            __DIR__.'/../../src/Resources/config',
        ]);
        $loader = new YamlFileLoader($containerBuilder, $locator);
        $loader->load('collectors.yaml');
        $loader->load('commands.yaml');
        $loader->load('handlers.yaml');
        $loader->load('providers.yaml');
        $loader->load('registries.yaml');
        $loader->load('resolvers.yaml');

        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            if ($serviceClass === $definition->getClass()) {
                $this->assertTrue(true);
                return;
            }
        }

        foreach ($containerBuilder->getAliases() as $id => $alias) {
            if ($serviceClass === $id) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->assertTrue(false);
    }

    /**
     * Assert that an exception is raised in the given code.
     *
     * @param $exceptionClass
     * @param Closure $closure
     */
    protected function assertExceptionRaised($exceptionClass, Closure $closure)
    {
        $e = null;
        $isClassValid = false;
        $message = '';

        try {
            $closure();
        } catch (Exception $e) {
            if (get_class($e) == $exceptionClass) {
                $isClassValid = true;
            }
            $message = $e->getMessage();
        }
        $this->assertNotNull($e, 'No Exception was thrown');
        $this->assertTrue($isClassValid, sprintf('Expected %s, got %s (Exception message : "%s")',
            $exceptionClass,
            get_class($e),
            $message
        ));
    }
}
