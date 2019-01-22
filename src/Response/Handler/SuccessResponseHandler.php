<?php

namespace LAG\SmokerBundle\Response\Handler;

use LAG\SmokerBundle\Exception\Exception;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class SuccessResponseHandler implements ResponseHandlerInterface
{
    /**
     * @var array
     */
    private $routes;

    /**
     * SuccessResponseHandler constructor.
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

        return key_exists('status_code', $this->routes[$routeName]['handlers']);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $routeName, Crawler $crawler, Client $client): void
    {
        if (null === $client->getResponse()) {
            throw new Exception('The client has no response. It should make a request before handle a response');
        }
        $responseCode = $client->getResponse()->getStatus();

        if (Response::HTTP_OK !== $client->getResponse()->getStatus()) {
            throw new Exception('Excepted code 200, got '.$responseCode.' for route '.$routeName);
        }
    }
}
