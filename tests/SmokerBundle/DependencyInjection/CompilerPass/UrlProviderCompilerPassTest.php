<?php

namespace LAG\SmokerBundle\Tests\DependencyInjection\CompilerPass;

use LAG\SmokerBundle\DependencyInjection\CompilerPass\UrlProviderCompilerPass;
use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Tests\Fake\FakeUrlProvider;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class UrlProviderCompilerPassTest extends BaseTestCase
{
    public function testCompilerPass()
    {
        $container = new ContainerBuilder();
        $container->setDefinition(UrlProviderRegistry::class, new Definition());
        $container->setDefinition(FakeUrlProvider::class, new Definition(FakeUrlProvider::class));

        $pass = new UrlProviderCompilerPass();
        $pass->process($container);

        $registry = $container->getDefinition(UrlProviderRegistry::class);

        $this->assertCount(1, $registry->getMethodCalls());
        $this->assertCount(2, $registry->getMethodCalls()[0]);
        $this->assertEquals([
            'add',
            [new Reference(FakeUrlProvider::class)],
        ], $registry->getMethodCalls()[0]);
    }

    public function testCompilerPassWithoutRegistry()
    {
        $container = new ContainerBuilder();

        $pass = new UrlProviderCompilerPass();
        $pass->process($container);
        $this->assertTrue(true);
    }
}
