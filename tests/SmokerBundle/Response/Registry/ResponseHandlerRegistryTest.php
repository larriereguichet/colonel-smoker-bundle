<?php

namespace LAG\SmokerBundle\Tests\Response\Registry;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Response\Handler\ResponseHandlerInterface;
use LAG\SmokerBundle\Response\Registry\ResponseHandlerRegistry;
use LAG\SmokerBundle\Tests\BaseTestCase;

class ResponseHandlerRegistryTest extends BaseTestCase
{
    public function testServiceExists()
    {
        $this->assertServiceExists(ResponseHandlerRegistry::class);
    }

    public function testRegistry()
    {
        $handler = $this->createMock(ResponseHandlerInterface::class);

        $registry = new ResponseHandlerRegistry();
        $registry->add('my_handler', $handler);

        $this->assertEquals($handler, $registry->get('my_handler'));
        $this->assertTrue($registry->has('my_handler'));
        $this->assertFalse($registry->has(('wrong_handler')));

        $this->assertCount(1, $registry->all());
        $this->assertEquals($handler, $registry->all()['my_handler']);

        $this->assertExceptionRaised(Exception::class, function () use ($registry) {
            $registry->get('wrong_handler');
        });
    }
}
