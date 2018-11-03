<?php

namespace LAG\SmokerBundle\Command;

use LAG\SmokerBundle\Message\MessageCollectorInterface;
use LAG\SmokerBundle\Url\Provider\UrlProviderInterface;
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
    protected $providerConfiguration;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var MessageCollectorInterface
     */
    protected $messageCollector;

    /**
     * GenerateCacheCommand constructor.
     *
     * @param string                    $cacheDir
     * @param array                     $providerConfiguration
     * @param UrlProviderRegistry       $registry
     * @param MessageCollectorInterface $messageCollector
     */
    public function __construct(
        string $cacheDir,
        array $providerConfiguration,
        UrlProviderRegistry $registry,
        MessageCollectorInterface $messageCollector
    ) {
        $this->registry = $registry;
        $this->cacheDir = $cacheDir;
        $this->providerConfiguration = $providerConfiguration;

        parent::__construct();
        $this->messageCollector = $messageCollector;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate the urls cache used in the smoke tests. Urls are gathered by the urls providers')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fileSystem = new Filesystem();
        $this->messageCollector->initialize();
        $io = new SymfonyStyle($input, $output);
        $io->title('Smoker Generate Cache Command');

        // Initialize the cache file
        $io->text('Creating cache file...');
        $cacheFile = $this->createCacheFile();

        // Gather and configure the urls providers
        $io->text('Fetching urls providers...');
        $providers = $this->getProviders();
        $urlCount = 0;

        foreach ($providers as $providerName => $providerData) {
            $io->text('Processing the url provider "'.$providerName.'"...');
            $provider = $providerData['provider'];
            $options = $providerData['options'];
            $urls = $provider->getCollection($options);

            $io->progressStart($urls->count());

            foreach ($urls->all() as $urlItem) {
                $providerCache = $urlItem->serialize()."\n";
                $this->fileSystem->appendToFile($cacheFile, $providerCache);
                $io->progressAdvance();
                $urlCount++;
            }
            $io->progressFinish();
        }

        if ($urlCount > 0) {
            $io->success('The cache has been generated with '.$urlCount.' urls in '.$cacheFile);
        } else {
            $io->warning('No url was found in the configured url providers');
        }
    }

    /**
     * @return UrlProviderInterface[][]
     */
    protected function getProviders(): array
    {
        $providers = $this->registry->all();
        $allowedProviders = [];
        $resolver = new OptionsResolver();

        foreach ($providers as $id => $provider) {
            $resolver->clear();

            if (key_exists($id, $this->providerConfiguration)) {
                if (null === $this->providerConfiguration[$id]) {
                    $this->providerConfiguration[$id] = [];
                }
                $provider->configureOptions($resolver);
                $options = $resolver->resolve($this->providerConfiguration[$id]);

                $allowedProviders[$id]['provider'] = $provider;
                $allowedProviders[$id]['options'] = $options;
            }
        }

        return $allowedProviders;
    }

    protected function createCacheFile(): string
    {
        $cacheFile = $this->cacheDir.'/smoker/smoker.cache';
        $this->fileSystem->dumpFile($cacheFile, '');

        return $cacheFile;
    }
}
