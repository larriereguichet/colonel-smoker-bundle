<?php

namespace LAG\SmokerBundle\Command;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Message\MessageCollectorInterface;
use LAG\SmokerBundle\Response\Registry\ResponseHandlerRegistry;
use Goutte\Client;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

class SmokeCommand extends Command
{
    protected static $defaultName = 'smoker:smoke';

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var ResponseHandlerRegistry
     */
    private $responseHandlerRegistry;

    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var string
     */
    private $errorsFile;

    /**
     * @var string
     */
    private $successFile;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var UrlProviderRegistry
     */
    private $urlProviderRegistry;

    /**
     * @var MessageCollectorInterface
     */
    private $messageCollector;

    /**
     * SmokeCommand constructor.
     *
     * @param string                    $cacheDir
     * @param ResponseHandlerRegistry   $responseHandlerRegistry
     * @param UrlProviderRegistry       $urlProviderRegistry
     * @param MessageCollectorInterface $messageCollector
     * @param \Twig_Environment         $twig
     */
    public function __construct(
        string $cacheDir,
        ResponseHandlerRegistry $responseHandlerRegistry,
        UrlProviderRegistry $urlProviderRegistry,
        MessageCollectorInterface $messageCollector,
        \Twig_Environment $twig
    )
    {
        parent::__construct();

        $this->cacheDir = $cacheDir;
        $this->responseHandlerRegistry = $responseHandlerRegistry;
        $this->twig = $twig;
        $this->fileSystem = new Filesystem();
        $this->urlProviderRegistry = $urlProviderRegistry;
        $this->messageCollector = $messageCollector;
    }

    protected function configure()
    {
        $this
            ->addOption(
                'stop-on-failure',
                null,
                InputOption::VALUE_OPTIONAL,
                'Stop all tests if an error is detected',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Smoker Tests');

        $this->io->note('Initialize results cache...');
        $this->initializeCache();

        $host = 'http://127.0.0.1:8000/index.php';

        $this->smoke($host, (bool)$input->getOption('stop-on-failure'));

        $this->io->note('Generating results report...');
        $this->generateResults();
        $this->io->text('The results report has been generated here file://'.$this->cacheDir.'/smoker/results.html');
    }

    private function initializeCache()
    {
        $fileSystem = new Filesystem();

        $this->cacheFile = $this->cacheDir.'/smoker/smoker.cache';
        $this->errorsFile = $this->cacheDir.'/smoker/smoker.error';
        $this->successFile = $this->cacheDir.'/smoker/smoker.success';

        $fileSystem->dumpFile($this->errorsFile, '');
        $fileSystem->dumpFile($this->successFile, '');
    }

    /**
     * @param string       $host
     * @param bool         $stopOnFailure
     */
    private function smoke(string $host, bool $stopOnFailure)
    {
        if (!$this->fileSystem->exists($this->cacheFile)) {
            $this->io->warning('The cache file is not generated. Nothing will be done.');
            $this->io->note('The cache can be generated with the command bin/console smoker:generate-cache');

            return;
        }
        $handle = fopen($this->cacheFile, "r");

        if ($handle) {
            $this->io->text('Start reading urls in cache...');

            while (($row = fgets($handle, 4096)) !== false) {
                $data = unserialize($row);
                $url = $host.$data['location'];
                $this->processRow($url, $stopOnFailure);

                if (Output::VERBOSITY_DEBUG === $this->io->getVerbosity()) {
                    $this->io->write('  '.Helper::formatMemory(memory_get_usage(true)));
                }
                $this->io->newLine();
                gc_collect_cycles();
            }

            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }
    }

    private function generateResults()
    {
        $this->errorsFile = $this->cacheDir.'/smoker/smoker.error';
        $this->successFile = $this->cacheDir.'/smoker/smoker.success';

        $raw = file_get_contents($this->successFile);
        $successData = explode(PHP_EOL, $raw);
        array_pop($successData);
        $successData = array_map(function ($serializedData) {
            return unserialize($serializedData);
        }, $successData);

        $raw = file_get_contents($this->errorsFile);
        $errorData = explode(PHP_EOL, $raw);
        array_pop($errorData);
        $errorData = array_map(function ($serializedData) {
            return unserialize($serializedData);
        }, $errorData);

        $content = $this->twig->render('@LAGSmoker/Results/results.html.twig', [
            'successData' => $successData,
            'errorData' => $errorData,
        ]);

        $this->fileSystem->dumpFile($this->cacheDir.'/smoker/results.html', $content);
    }

    private function processRow(string $location, bool $stopOnFailure): void
    {
        $this->io->write('Processing '.$location.'...');

        // Create a new empty client and fetch the request data
        $client = new Client();
        $crawler = $client->request('get', $location);
        $response = $client->getResponse();

        try {
            $routeName = $this->match($location);
        } catch (\Exception $exception) {
            $this
                ->messageCollector
                ->addError(
                    'An error has occurred when matching the path "'.$location.'"',
                    500,
                    $exception
                );
            $this->io->write('...[<comment>WARN</comment>]');

            return;
        }

        foreach ($this->responseHandlerRegistry->all() as $responseHandlerName => $responseHandler) {
            if ($responseHandler->supports($routeName)) {
                continue;
            }

            try {
                $responseHandler->handle($routeName, $crawler, $client);
                $this->io->write('...[<info>OK</info>]');
            } catch (\Exception $exception) {
                $this->io->note('Error in the response handler "'.$responseHandlerName.'"');

                if (true === $stopOnFailure) {
                    throw $exception;
                }
                $this
                    ->messageCollector
                    ->addError(
                        'An error has occurred when processing the url '.$location,
                        $response->getStatus(),
                        $exception
                    )
                ;
                $this->io->write('...[<error>WARN</error>]');
                $this->io->error($exception->getMessage());
            }
        }
        $this->messageCollector->flush();
    }

    /**
     * Return the route associated to the given path.
     *
     * @param string $path
     *
     * @return string
     *
     * @throws Exception
     */
    private function match(string $path): string
    {
        foreach ($this->urlProviderRegistry->all() as $provider) {
            if (!$provider->supports($path)) {
                continue;
            }

            return $provider->match($path);
        }

        throw new Exception('The path "'.$path.'" is not supported by an url provider');
    }
}
