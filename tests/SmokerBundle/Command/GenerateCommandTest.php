<?php

namespace LAG\SmokerBundle\Tests\Command;

use LAG\SmokerBundle\Command\GenerateCacheCommand;
use LAG\SmokerBundle\Message\MessageCollectorInterface;
use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $command = $this->createCommand();
        $tester = new CommandTester($command);
        $result = $tester->execute([

        ]);

        $this->assertEquals(0, $result);
    }

    private function createCommand()
    {
        $urlProviderRegistry = $this->createMock(UrlProviderRegistry::class);
        $messageCollector = $this->createMock(MessageCollectorInterface::class);

        $command = new GenerateCacheCommand(
            __DIR__.'/../../../var/cache/test/smoker',
            [],
            $urlProviderRegistry,
            $messageCollector
        );

        return $command;
    }
}
