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
     * @var Client
     */
    private $client;

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
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Smoker Tests');

        $this->io->note('Initialize results cache...');
        $this->initializeCache();

        $host = 'http://127.0.0.1:8000/index.php';

        $success = $this->smoke($host, (bool)$input->getOption('stop-on-failure'));

        $this->io->note('Generating results report...');
        $this->generateResults();
        $this->io->text('The results report has been generated here file://'.$this->cacheDir.'/smoker/results.html');

        return $success;
    }

    private function initializeCache(): void
    {
        $this->cacheFile = $this->cacheDir.'/smoker/smoker.cache';
        $this->messageCollector->initialize();

        // Create a new client. Cookies are by default enabled
        $this->client = new Client();
        $this->client->followRedirects(false);
    }

    /**
     * @param string $host
     * @param bool   $stopOnFailure
     *
     * @return bool
     */
    private function smoke(string $host, bool $stopOnFailure): bool
    {
        if (!$this->fileSystem->exists($this->cacheFile)) {
            $this->io->warning('The cache file is not generated. Nothing will be done.');
            $this->io->note('The cache can be generated with the command bin/console smoker:generate-cache');

            return false;
        }
        $handle = fopen($this->cacheFile, "r");
        $allResponseInSuccess = true;

        if ($handle) {
            $this->io->text('Start reading urls in cache...');

            while (($row = fgets($handle, 4096)) !== false) {
                $data = unserialize($row);
                $success = $this->processRow($host, $data['location'], $stopOnFailure);

                if (Output::VERBOSITY_DEBUG === $this->io->getVerbosity()) {
                    $this->io->write('  '.Helper::formatMemory(memory_get_usage(true)));
                }
                if (!$success) {
                    $allResponseInSuccess = false;
                }
                $this->io->newLine();
                gc_collect_cycles();
            }

            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }

        return $allResponseInSuccess;
    }

    private function generateResults(): void
    {
        $messages = $this->messageCollector->read();

        $content = $this->twig->render('@LAGSmoker/Results/results.html.twig', [
            'messages' => $messages,
        ]);

        $this->fileSystem->dumpFile($this->cacheDir.'/smoker/results.html', $content);
    }

    /**
     * @param string $host
     * @param string $location
     * @param bool   $stopOnFailure
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function processRow(string $host, string $location, bool $stopOnFailure): bool
    {
        $this->io->write('Processing '.$host.$location.'...');

        // Create a new empty client and fetch the request data
        $crawler = $this->client->request('get', $host.$location);
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

            return false;
        }
        $responseHandled = false;
        $responseInError = true;

        foreach ($this->responseHandlerRegistry->all() as $responseHandlerName => $responseHandler) {
            if (!$responseHandler->supports($routeName)) {
                continue;
            }
            $responseHandled = true;

            try {
                $responseHandler->handle($routeName, $crawler, $this->client);

                $this->io->write('...[<info>OK</info>]');
                $this
                    ->messageCollector
                    ->addSuccess($location, 'Success for handler', $response->getStatus())
                ;
            } catch (\Exception $exception) {
                if (true === $stopOnFailure) {
                    throw $exception;
                }
                $this
                    ->messageCollector
                    ->addError(
                        $location,
                        'An error has occurred when processing the url '.$host.$location,
                        $response->getStatus(),
                        $exception
                    )
                ;
                $this->io->write('...[<error>KO</error>]');
                $this->io->note('Error in the response handler "'.$responseHandlerName.'"');
                $this->io->error($exception->getMessage());
                $responseInError = true;
            }
        }

        if (!$responseHandled) {
            $this->io->write('...[<comment>WARN</comment>]');
            $this
                ->messageCollector
                ->addWarning($location, 'The response of the url is not handle by any response handler')
            ;
        }
        $this->messageCollector->flush();

        return !$responseInError;
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
