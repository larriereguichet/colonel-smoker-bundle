<?php

namespace LAG\SmokerBundle\Response\Handler;

use LAG\SmokerBundle\Exception\Exception;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class ResponseCodeHandler implements ResponseHandlerInterface
{
    private $name = 'response_code';

    /**
     * @var array
     */
    private $routes;

    /**
     * ResponseCodeHandler constructor.
     *
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $routeName): bool
    {
        if (!key_exists($routeName, $this->routes)) {
            return false;
        }

        if (!key_exists('handlers', $this->routes[$routeName])) {
            return false;
        }

        return key_exists($this->name, $this->routes[$routeName]['handlers']);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $routeName, Crawler $crawler, Client $client, array $options = []): void
    {
        $expectedResponseCode = Response::HTTP_OK;
        $configuration = $this->routes[$routeName]['handlers'][$this->name];

        if (!is_array($configuration)) {
            $expectedResponseCode = $configuration;
        } elseif (key_exists('code', $configuration)) {
            $expectedResponseCode = $configuration['code'];
        }

        if (null === $client->getResponse()) {
            throw new Exception('The client has no response. It should make a request before handle a response');
        }
        $responseCode = $client->getResponse()->getStatus();

        if ((string) $expectedResponseCode !== (string) $responseCode) {
            throw new Exception('Excepted code '.$expectedResponseCode.', got '.$responseCode.' for route '.$routeName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }
}
