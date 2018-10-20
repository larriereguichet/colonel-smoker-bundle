<?php

namespace LAG\SmokerBundle\Command;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Response\Registry\ResponseHandlerRegistry;
use Goutte\Client;
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
    protected $cacheDir;

    /**
     * @var ResponseHandlerRegistry
     */
    protected $registry;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $cacheFile;

    /**
     * @var string
     */
    protected $errorsFile;

    /**
     * @var string
     */
    protected $successFile;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * SmokeCommand constructor.
     *
     * @param string                  $cacheDir
     * @param ResponseHandlerRegistry $registry
     * @param RouterInterface         $router
     * @param \Twig_Environment       $twig
     */
    public function __construct(
        string $cacheDir,
        ResponseHandlerRegistry $registry,
        RouterInterface $router,
        \Twig_Environment $twig
    ) {
        parent::__construct();

        $this->cacheDir = $cacheDir;
        $this->registry = $registry;
        $this->router = $router;
        $this->twig = $twig;
        $this->fileSystem = new Filesystem();
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
        $io = new SymfonyStyle($input, $output);
        $io->title('Smoker Tests');

        $io->note('Initialize results cache...');
        $this->initializeCache();

        $host = 'http://127.0.0.1:8000/index.php';

        $this->smoke($host, $io, (bool)$input->getOption('stop-on-failure'));

        $io->note('Generating results report...');
        $this->generateResults();
        $io->text('The results report has been generated here file://'.$this->cacheDir.'/smoker/results.html');
    }

    protected function getLineCount(string $cacheFile)
    {
        $count = 0;
        $handle = fopen($cacheFile, "r");

        if ($handle) {
            while (($row = fgets($handle, 4096)) !== false) {
                $count++;
            }

            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }

        return $count;
    }

    protected function initializeCache()
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
     * @param SymfonyStyle $io
     * @param bool         $stopOnFailure
     *
     * @throws Exception
     */
    protected function smoke(string $host, SymfonyStyle $io, bool $stopOnFailure)
    {
        if (!$this->fileSystem->exists($this->cacheFile)) {
            $io->warning('The cache file is not generated. Nothing will be done.');
            $io->note('The cache can be generated with the command bin/console smoker:generate-cache');

            return;
        }
        $handle = fopen($this->cacheFile, "r");

        if ($handle) {
            $io->text('Start reading urls in cache...');

            while (($row = fgets($handle, 4096)) !== false) {
                $data = unserialize($row);
                $url = $host.$data['location'];
                $io->write('Processing '.$url.'...');
                $client = new Client();
                $crawler = $client->request('get', $url);
                $response = $client->getResponse();

                $routeInfo = $this->router->match($data['location']);
                $hasError = false;

                foreach ($this->registry->all() as $responseHandlerName => $responseHandler) {
                    if ($responseHandler->supports($routeInfo['_route'])) {
                        try {
                            $responseHandler->handle($routeInfo['_route'], $crawler, $client);
                        } catch (Exception $exception) {
                            $io->note('Error in '.$responseHandlerName);

                            if (true === $stopOnFailure) {
                                throw $exception;
                            }
                            $error = serialize([
                                'url' => $url,
                                'message' => $exception->getMessage(),
                                'handler' => $responseHandlerName,
                                'responseCode' => $response->getStatus(),
                            ]);
                            $this->fileSystem->appendToFile($this->errorsFile, $error.PHP_EOL);
                            $io->error($exception->getMessage());

                            $hasError = true;
                        }
                    }
                }

                if (!$hasError) {
                    $success = serialize([
                        'url' => $url,
                        'responseCode' => $response->getStatus(),
                    ]);
                    $this->fileSystem->appendToFile($this->successFile, $success.PHP_EOL);
                    $io->write('...[<info>OK</info>]');
                }

                if (Output::VERBOSITY_DEBUG === $io->getVerbosity()) {
                    $io->write('  '.Helper::formatMemory(memory_get_usage(true)));
                }
                $io->newLine();
                gc_collect_cycles();
            }

            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }
    }

    protected function generateResults()
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
}
