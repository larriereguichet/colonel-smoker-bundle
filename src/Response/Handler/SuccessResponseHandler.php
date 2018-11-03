<?php

namespace LAG\SmokerBundle\Response\Handler;

use LAG\SmokerBundle\Exception\Exception;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class SuccessResponseHandler implements ResponseHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function supports(string $routeName): bool
    {
        return true;
    }

    /**
     * @inheritdoc
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
