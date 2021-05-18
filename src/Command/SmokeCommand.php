<?php

namespace LAG\SmokerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SmokeCommand extends Command
{
    protected static $defaultName = 'smoker:smoke';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

    }
}
