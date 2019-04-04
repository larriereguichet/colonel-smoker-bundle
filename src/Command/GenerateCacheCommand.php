<?php

namespace LAG\SmokerBundle\Command;

use LAG\SmokerBundle\Message\MessageCollectorInterface;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    /**
     * @var array
     */
    protected $routesConfiguration;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var MessageCollectorInterface
     */
    protected $messageCollector;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * GenerateCacheCommand constructor.
     *
     * @param string                    $cacheDir
     * @param array                     $routesConfiguration
     * @param UrlProviderRegistry       $registry
     * @param MessageCollectorInterface $messageCollector
     */
    public function __construct(
        string $cacheDir,
        array $routesConfiguration,
        UrlProviderRegistry $registry,
        MessageCollectorInterface $messageCollector
    ) {
        $this->registry = $registry;
        $this->cacheDir = $cacheDir;
        $this->routesConfiguration = $routesConfiguration;

        parent::__construct();

        $this->messageCollector = $messageCollector;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate the urls cache used in the smoke tests. Urls are gathered by the urls providers')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->fileSystem = new Filesystem();
        $this->messageCollector->initialize();
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Smoker Generate Cache Command');

        // Create an empty cache file to initialize urls collecting
        $cacheFile = $this->createCacheFile();
        $urlCount = 0;

        foreach ($this->registry->all() as $provider) {
            $this->io->text('Processing the url provider "'.$provider->getName().'"...');

            $resolver = new OptionsResolver();
            $provider->configure($resolver);
            $urls = $provider->getCollection($resolver->resolve());

            $this->io->progressStart($urls->count());

            foreach ($urls->all() as $urlItem) {
                $providerCache = $urlItem->serialize().PHP_EOL;
                $this->fileSystem->appendToFile($cacheFile, $providerCache);
                $this->io->progressAdvance();
                ++$urlCount;
            }
            $this->io->progressFinish();
        }

        if ($urlCount > 0) {
            $this->io->success('The cache has been generated with '.$urlCount.' urls in '.$cacheFile);
        } else {
            $this->io->warning('No url was found in the configured url providers');
        }
    }

    protected function createCacheFile(): string
    {
        $this->io->text('Creating cache file...');
        $cacheFile = $this->cacheDir.'/smoker/smoker.cache';
        $this->fileSystem->dumpFile($cacheFile, '');

        return $cacheFile;
    }
}
