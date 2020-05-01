<?php

namespace LAG\SmokerBundle\Command;

use Goutte\Client;
use LAG\SmokerBundle\Message\MessageCollectorInterface;
use LAG\SmokerBundle\Response\Registry\ResponseHandlerRegistry;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use LAG\SmokerBundle\Url\Url;
use LAG\SmokerBundle\Url\UrlInfo;
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
use Twig\Environment;

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
     * @var Environment
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
     * @var array
     */
    protected $routing;

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
     */
    public function __construct(
        string $cacheDir,
        array $routing,
        array $routes,
        ResponseHandlerRegistry $responseHandlerRegistry,
        UrlProviderRegistry $urlProviderRegistry,
        MessageCollectorInterface $messageCollector,
        Environment $twig
    ) {
        parent::__construct();

        $this->cacheDir = $cacheDir;
        $this->responseHandlerRegistry = $responseHandlerRegistry;
        $this->twig = $twig;
        $this->fileSystem = new Filesystem();
        $this->urlProviderRegistry = $urlProviderRegistry;
        $this->messageCollector = $messageCollector;
        $this->routing = $routing;
        $this->routes = $routes;
    }

    protected function configure()
    {
        $this
            ->addOption('stop-on-failure', null, InputOption::VALUE_NONE, 'Stop all tests if an error is detected')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Smoker Tests');

        $this->initializeCommand($input);
        $this->smoke();
        $this->generateResults();
    }

    protected function initializeCommand(InputInterface $input)
    {
        $this->io->note('Initialize results cache...');
        $this->cacheFile = $this->cacheDir.'/smoker/smoker.cache';
        $this->messageCollector->initialize();

        if ($input->getOption('stop-on-failure')) {
            $this->stopOnFailure = true;
        }
        // Create a new client. Cookies are by default enabled
        $this->client = new Client();
        $this->client->followRedirects(false);
    }

    protected function smoke()
    {
        if (!$this->fileSystem->exists($this->cacheFile)) {
            $this->io->warning('The cache file is not generated. Nothing will be done.');
            $this->io->note('The cache can be generated with the command bin/console smoker:generate-cache');

            return;
        }
        $handle = fopen($this->cacheFile, 'r');

        if (false !== $handle) {
            $this->io->text('Start reading urls in cache...');

            while (false !== ($row = fgets($handle, 4096))) {
                $url = Url::deserialize($row);
                $this->processRow($url);

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

    protected function processRow(Url $url): void
    {
        $this->io->write('Processing '.$url->getLocation().'...');

        // Create a new empty client and fetch the request data
        $crawler = $this->client->request('get', $url->getLocation());
        /** @var Response $response */
        $response = $this->client->getResponse();

        try {
            $urlInfo = $this->urlProviderRegistry->match($url->getLocation());
        } catch (\Exception $exception) {
            $this
                ->messageCollector
                ->addError(
                    $url->getLocation(),
                    $exception->getMessage(),
                    500,
                    $exception
                );
            $this->io->write('...[<error>KO</error>]');
            $this->messageCollector->flush();

            return;
        }
        $responseHandled = $this->handleResponse($urlInfo, $url, $crawler, $response);

        if (!$responseHandled) {
            $this->io->write('...[<comment>WARN</comment>]');
            $this
                ->messageCollector
                ->addWarning($url->getLocation(), 'The response of the url is not handle by any response handler')
            ;
        }

        $this->messageCollector->flush();
    }

    /**
     * @throws \Exception
     */
    protected function handleResponse(UrlInfo $urlInfo, Url $url, Crawler $crawler, Response $response): bool
    {
        $responseHandled = false;

        foreach ($this->responseHandlerRegistry->all() as $responseHandler) {
            if (!$responseHandler->supports($urlInfo->getRouteName())) {
                continue;
            }
            $responseHandled = true;

            try {
                $routeOptions = $this->routes[$urlInfo->getRouteName()];
                $providerOptions = [];

                if (key_exists($responseHandler->getName(), $routeOptions['handlers'])) {
                    $providerOptions = $routeOptions['handlers'][$responseHandler->getName()];

                    if (!is_array($providerOptions)) {
                        $providerOptions = [
                            $providerOptions,
                        ];
                    }
                }
                $providerOptions['_url_info'] = $urlInfo;

                if ($url->hasOption('identifiers')) {
                    $providerOptions['_identifiers'] = $url->getOption('identifiers');
                }
                $responseHandler->handle($urlInfo->getRouteName(), $crawler, $this->client, $providerOptions);

                $this->io->write('...[<info>OK</info>]');
                $this
                    ->messageCollector
                    ->addSuccess($url->getLocation(), 'Success for handler', $response->getStatus())
                ;
            } catch (\Exception $exception) {
                $message = sprintf('An error has occurred when processing the url %s', $url->getLocation());
                $this
                    ->messageCollector
                    ->addError($url->getLocation(), $message, $response->getStatus(), $exception)
                ;

                if ($this->io->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $this->io->error($message.': '.$exception->getMessage());

                    if ($this->io->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $this->io->text($exception->getTraceAsString());
                    }
                }

                if ($this->stopOnFailure) {
                    $this->generateResults();

                    throw $exception;
                }
                $this->io->write('...[<error>KO</error>]');
            }
        }

        return $responseHandled;
    }
}
