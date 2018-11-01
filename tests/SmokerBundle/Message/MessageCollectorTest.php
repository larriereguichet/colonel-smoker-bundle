<?php

namespace LAG\SmokerBundle\Tests\Message;

use LAG\SmokerBundle\Message\MessageCollector;
use LAG\SmokerBundle\Tests\BaseTestCase;

class MessageCollectorTest extends BaseTestCase
{
    public function testServiceExists()
    {
        $this->assertServiceExists(MessageCollector::class);
    }

    public function testAddError()
    {
        $exception = new \Exception();

        $collector = new MessageCollector();
        $collector->addError('A beautiful error', 666, $exception);

        $this->assertCount(1, $collector->getErrors());
        $this->assertCount(0, $collector->getSuccess());
        $this->assertCount(0, $collector->getWarnings());
        $this->assertEquals('A beautiful error', $collector->getErrors()[0]['message']);
        $this->assertEquals(666, $collector->getErrors()[0]['code']);
        $this->assertEquals($exception, $collector->getErrors()[0]['exception']);
    }

    public function testAddSuccess()
    {
        $collector = new MessageCollector();
        $collector->addSuccess('Everything is wonderful');

        $this->assertCount(1, $collector->getSuccess());
        $this->assertCount(0, $collector->getWarnings());
        $this->assertCount(0, $collector->getErrors());
        $this->assertEquals('Everything is wonderful', $collector->getSuccess()[0]);
    }

    public function testAddWarning()
    {
        $collector = new MessageCollector();
        $collector->addWarning('Warning ! Nuclear launch detected');

        $this->assertCount(0, $collector->getSuccess());
        $this->assertCount(1, $collector->getWarnings());
        $this->assertCount(0, $collector->getSuccess());
        $this->assertEquals('Warning ! Nuclear launch detected', $collector->getWarnings()[0]);
    }
}
