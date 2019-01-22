<?php

namespace LAG\SmokerBundle\Tests\Message;

use LAG\SmokerBundle\Message\MessageCollector;
use LAG\SmokerBundle\Tests\BaseTestCase;

class MessageCollectorTest extends BaseTestCase
{
    private $cacheDirectory = __DIR__.'/../../../var/cache/';

    public function testServiceExists()
    {
        $this->assertServiceExists(MessageCollector::class);
    }

    public function testAddError()
    {
        $exception = new \Exception();

        $collector = new MessageCollector($this->cacheDirectory);
        $collector->addError('http://my-little-url', 'A beautiful error', 666, $exception);

        $this->assertCount(1, $collector->getErrors());
        $this->assertCount(0, $collector->getSuccess());
        $this->assertCount(0, $collector->getWarnings());
        $this->assertEquals('http://my-little-url', $collector->getErrors()[0]['url']);
        $this->assertEquals('A beautiful error', $collector->getErrors()[0]['message']);
        $this->assertEquals(666, $collector->getErrors()[0]['code']);
        $this->assertEquals($exception->getTraceAsString(), $collector->getErrors()[0]['stacktrace']);
    }

    public function testAddSuccess()
    {
        $collector = new MessageCollector($this->cacheDirectory);
        $collector->addSuccess('http://my-little-url', 'Everything is wonderful', 200);

        $this->assertCount(1, $collector->getSuccess());
        $this->assertCount(0, $collector->getWarnings());
        $this->assertCount(0, $collector->getErrors());
        $this->assertEquals('Everything is wonderful', $collector->getSuccess()[0]['message']);
        $this->assertEquals(200, $collector->getSuccess()[0]['code']);
        $this->assertEquals('http://my-little-url', $collector->getSuccess()[0]['url']);
    }

    public function testAddWarning()
    {
        $collector = new MessageCollector($this->cacheDirectory);
        $collector->addWarning('http://my-little-url', 'Alert, nuclear launch detected', 400);

        $this->assertCount(0, $collector->getSuccess());
        $this->assertCount(1, $collector->getWarnings());
        $this->assertCount(0, $collector->getErrors());
        $this->assertEquals('Alert, nuclear launch detected', $collector->getWarnings()[0]['message']);
        $this->assertEquals(400, $collector->getWarnings()[0]['code']);
        $this->assertEquals('http://my-little-url', $collector->getWarnings()[0]['url']);
    }
}
