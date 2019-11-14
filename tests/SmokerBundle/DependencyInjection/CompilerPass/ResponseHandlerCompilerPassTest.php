<?php

namespace LAG\SmokerBundle\Tests\DependencyInjection\CompilerPass;

use LAG\SmokerBundle\DependencyInjection\CompilerPass\ResponseHandlerCompilerPass;
use LAG\SmokerBundle\Response\Registry\ResponseHandlerRegistry;
use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Tests\Fake\FakeResponseHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ResponseHandlerCompilerPassTest extends BaseTestCase
{
    public function testCompilerPass()
    {
        $container = new ContainerBuilder();
        $container->setDefinition(ResponseHandlerRegistry::class, new Definition());
        $container->setDefinition(FakeResponseHandler::class, new Definition(FakeResponseHandler::class));

        $pass = new ResponseHandlerCompilerPass();
        $pass->process($container);

        $registry = $container->getDefinition(ResponseHandlerRegistry::class);

        $this->assertCount(1, $registry->getMethodCalls());
        $this->assertCount(2, $registry->getMethodCalls()[0]);
        $this->assertEquals([
            'add',
            [FakeResponseHandler::class, new Reference(FakeResponseHandler::class)],
        ], $registry->getMethodCalls()[0]);
    }

    public function testCompilerPassWithoutRegistry()
    {
        $container = new ContainerBuilder();

        $pass = new ResponseHandlerCompilerPass();
        $pass->process($container);
        $this->assertTrue(true);
    }
}
