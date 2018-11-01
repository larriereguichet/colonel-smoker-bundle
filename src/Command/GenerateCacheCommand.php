<?php

namespace LAG\SmokerBundle\Command;

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
     * GenerateCacheCommand constructor.
     *
     * @param string              $cacheDir
     * @param array               $providerConfiguration
     * @param UrlProviderRegistry $registry
     */
    public function __construct(string $cacheDir, array $providerConfiguration, UrlProviderRegistry $registry)
    {
        $this->registry = $registry;
        $this->cacheDir = $cacheDir;
        $this->providerConfiguration = $providerConfiguration;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate the urls cache used in the smoke tests. Urls are gathered by the urls providers')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Initialize the cache file
        $cacheFile = $this->cacheDir.'/smoker/smoker.cache';
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($cacheFile, '');

        // Gather and configure the urls providers
        $providers = $this->getProviders();
        $atLeastOneUrlProvided = false;

        foreach ($providers as $providerName => $providerData) {
            $io->text('Processing "'.$providerName.'" url provider...');

            $provider = $providerData['provider'];
            $options = $providerData['options'];

            $io->progressStart($provider->getCollection($options)->count());

            foreach ($provider->getCollection($options)->all() as $urlItem) {
                $providerCache = $urlItem->serialize()."\n";
                $fileSystem->appendToFile($cacheFile, $providerCache);
                $io->progressAdvance();
                $atLeastOneUrlProvided = true;
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

        if ($atLeastOneUrlProvided) {
            $io->success('The cache has been generated in '.$cacheFile);
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
}
