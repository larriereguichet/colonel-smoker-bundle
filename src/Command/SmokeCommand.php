<?php

namespace LAG\SmokerBundle\Command;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Message\MessageCollectorInterface;
use LAG\SmokerBundle\Response\Registry\ResponseHandlerRegistry;
use Goutte\Client;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;

class SmokeCommand extends Command
{
    protected static $defaultName = 'smoker:smoke';

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var ResponseHandlerRegistry
     */
    protected $responseHandlerRegistry;

    /**
     * @var string
     */
    protected $cacheFile;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var UrlProviderRegistry
     */
    protected $urlProviderRegistry;

    /**
     * @var MessageCollectorInterface
     */
    protected $messageCollector;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var bool
     */
    protected $stopOnFailure = false;

    /**
     * @var array
     */
    protected $routes;

    /**
     * SmokeCommand constructor.
     *
     * @param string                    $cacheDir
     * @param string                    $host
     * @param array                     $routes
     * @param ResponseHandlerRegistry   $responseHandlerRegistry
     * @param UrlProviderRegistry       $urlProviderRegistry
     * @param MessageCollectorInterface $messageCollector
     * @param \Twig_Environment         $twig
     */
    public function __construct(
        string $cacheDir,
        string $host,
        array $routes,
        ResponseHandlerRegistry $responseHandlerRegistry,
        UrlProviderRegistry $urlProviderRegistry,
        MessageCollectorInterface $messageCollector,
        \Twig_Environment $twig
    ) {
        parent::__construct();

        $this->cacheDir = $cacheDir;
        $this->responseHandlerRegistry = $responseHandlerRegistry;
        $this->twig = $twig;
        $this->fileSystem = new Filesystem();
        $this->urlProviderRegistry = $urlProviderRegistry;
        $this->messageCollector = $messageCollector;
        $this->host = $host;
        $this->routes = $routes;
    }

    protected function configure()
    {
        $this
            ->addOption('stop-on-failure', null, InputOption::VALUE_NONE, 'Stop all tests if an error is detected')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'The host called by smoker')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Smoker Tests');

        $this->initializeCommand($input);
        $this->smoke($this->host);
        $this->generateResults();
    }

    protected function initializeCommand(InputInterface $input)
    {
        $this->io->note('Initialize results cache...');
        $this->cacheFile = $this->cacheDir.'/smoker/smoker.cache';
        $this->messageCollector->initialize();

        if ($input->getOption('host')) {
            $this->host = $input->getOption('host');
        }

        if ($input->getOption('stop-on-failure')) {
            $this->stopOnFailure = true;
        }

        // Create a new client. Cookies are by default enabled
        $this->client = new Client();
        $this->client->followRedirects(false);
    }

    /**
     * @param string $host
     */
    protected function smoke(string $host)
    {
        if (!$this->fileSystem->exists($this->cacheFile)) {
            $this->io->warning('The cache file is not generated. Nothing will be done.');
            $this->io->note('The cache can be generated with the command bin/console smoker:generate-cache');

            return;
        }
        $handle = fopen($this->cacheFile, 'r');

        if ($handle) {
            $this->io->text('Start reading urls in cache...');

            while (false !== ($row = fgets($handle, 4096))) {
                $data = unserialize($row);
                $this->processRow($host, $data['location']);

                if (Output::VERBOSITY_DEBUG === $this->io->getVerbosity()) {
                    $this->io->write('  '.Helper::formatMemory(memory_get_usage(true)));
                }
                $this->io->newLine();
                gc_collect_cycles();
            }

            if (!feof($handle)) {
                $this->io->error('An error has occurred when reading the cache');
            }
            fclose($handle);
        }
    }

    protected function generateResults()
    {
        $this->io->note('Generating results report...');
        $this->messageCollector->flush();
        $messages = $this->messageCollector->read();

        $content = $this->twig->render('@LAGSmoker/Results/results.html.twig', [
            'messages' => $messages,
        ]);
        $this->fileSystem->dumpFile($this->cacheDir.'/smoker/results.html', $content);
        $this->io->text('The results report has been generated here file://'.$this->cacheDir.'/smoker/results.html');
    }

    /**
     * @param string $host
     * @param string $location
     *
     * @throws \Exception
     */
    protected function processRow(string $host, string $location): void
    {
        $this->io->write('Processing '.$host.$location.'...');

        // Create a new empty client and fetch the request data
        $crawler = $this->client->request('get', $host.$location);
        /** @var Response $response */
        $response = $this->client->getResponse();

        try {
            $routeName = $this->match($location);
        } catch (\Exception $exception) {
            $this
                ->messageCollector
                ->addError(
                    $location,
                    'An error has occurred when matching the path "'.$location.'"',
                    500,
                    $exception
                );
            $this->io->write('...[<comment>WARN</comment>]');
            $this->messageCollector->flush();

            return;
        }
        $responseHandled = $this->handleResponse($routeName, $location, $crawler, $response);

        if (!$responseHandled) {
            $this->io->write('...[<comment>WARN</comment>]');
            $this
                ->messageCollector
                ->addWarning($location, 'The response of the url is not handle by any response handler')
            ;
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
    protected function match(string $path): string
    {
        foreach ($this->urlProviderRegistry->all() as $provider) {
            if (!$provider->supports($path)) {
                continue;
            }

            return $provider->match($path);
        }

        throw new Exception('The path "'.$path.'" is not supported by an url provider');
    }

    /**
     * @param string   $routeName
     * @param string   $location
     * @param Crawler  $crawler
     * @param Response $response
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function handleResponse(string $routeName, string $location, Crawler $crawler, Response $response): bool
    {
        $responseHandled = false;

        foreach ($this->responseHandlerRegistry->all() as $responseHandler) {
            if (!$responseHandler->supports($routeName)) {
                continue;
            }
            $responseHandled = true;

            try {
                $routeOptions = $this->routes[$routeName];
                $providerOptions = [];

                if (key_exists($responseHandler->getName(), $routeOptions['handlers'])) {
                    $providerOptions = $routeOptions['handlers'][$responseHandler->getName()];

                    if (!is_array($providerOptions)) {
                        $providerOptions = [
                            $providerOptions,
                        ];
                    }
                }
                $responseHandler->handle($routeName, $crawler, $this->client, $providerOptions);

                $this->io->write('...[<info>OK</info>]');
                $this
                    ->messageCollector
                    ->addSuccess($location, 'Success for handler', $response->getStatus())
                ;
            } catch (\Exception $exception) {
                $url = $this->host.$location;
                $message = sprintf(
                    'An error has occurred when processing the url %s : %s (%s)',
                    $url,
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                );
                $this
                    ->messageCollector
                    ->addError($location, $message, $response->getStatus(), $exception)
                ;
                if (true === $this->stopOnFailure) {
                    $this->generateResults();

                    throw $exception;
                }
                $this->io->write('...[<error>KO</error>]');
            }
        }

        return $responseHandled;
    }
}
