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
        $collector->addError('test.fr', 'A beautiful error', 666, $exception);

        $this->assertCount(1, $collector->getErrors());
        $this->assertCount(0, $collector->getSuccess());
        $this->assertCount(0, $collector->getWarnings());

        $this->assertEquals('A beautiful error', $collector->getErrors()[0]['message']);
        $this->assertEquals(666, $collector->getErrors()[0]['code']);
        $this->assertEquals('test.fr', $collector->getErrors()[0]['url']);
    }

    public function testAddSuccess()
    {
        $collector = new MessageCollector($this->cacheDirectory);
        $collector->addSuccess('test.fr', 'Everything is wonderful');

        $this->assertCount(1, $collector->getSuccess());
        $this->assertCount(0, $collector->getWarnings());
        $this->assertCount(0, $collector->getErrors());

        $this->assertEquals('Everything is wonderful', $collector->getSuccess()[0]['message']);
        $this->assertEquals('test.fr', $collector->getSuccess()[0]['url']);
    }

    public function testAddWarning()
    {
        $collector = new MessageCollector($this->cacheDirectory);
        $collector->addWarning('test.fr', 'Warning ! Nuclear launch detected');

        $this->assertCount(0, $collector->getSuccess());
        $this->assertCount(1, $collector->getWarnings());
        $this->assertCount(0, $collector->getSuccess());
        $this->assertEquals('Warning ! Nuclear launch detected', $collector->getWarnings()[0]['message']);
        $this->assertEquals('test.fr', $collector->getWarnings()[0]['url']);
    }
}
