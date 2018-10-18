<?php

namespace LAG\SmokerBundle\Command;

use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class GenerateCacheCommand extends Command
{
    protected static $defaultName = 'smoker:generate-cache';
    
    /**
     * @var UrlProviderRegistry
     */
    protected $registry;
    /**
     * @var string
     */
    protected $cacheDir;

    public function __construct(string $cacheDir, UrlProviderRegistry $registry)
    {
        $this->registry = $registry;
        $this->cacheDir = $cacheDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $cacheFile = $this->cacheDir.'/smoker/smoker.cache';

        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($cacheFile, '');

        foreach ($this->registry->all() as $providerName => $provider) {
            $io->text('Processing "'.$providerName.'" url provider');
            $io->progressStart($provider->getCollection()->count());


            foreach ($provider->getCollection()->all() as $urlItem) {
                $providerCache = $urlItem->serialize()."\n";
                $fileSystem->appendToFile($cacheFile, $providerCache);
                $io->progressAdvance();
            }
            $io->progressFinish();

            if (0 < count($provider->getIgnoredMessages())) {
                if ($io->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $warning = 'The following url have been ignored : '."\n";
                    foreach ($provider->getIgnoredMessages() as $routeName => $message) {
                        $warning .= $routeName.': "'.$message.'"'."\n";
                    }
                    $io->warning($warning);
                } else {
                    $io->warning('Some urls are ignored. Run with -v to have more information');
                }
            }

            if (0 < count($provider->getErrorMessages())) {
                if ($io->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $error = 'The following url have been in error :'."\n";

                    foreach ($provider->getErrorMessages() as $routeName => $message) {
                        $error .= $routeName.': "'.$message.'"'."\n";
                    }
                    $io->error($error);
                } else {
                    $io->warning('Some urls are in error. Run with -v to have more information');
                }
            }
        }
        $io->success('The cache has been generated in '.$cacheFile);
    }
}
