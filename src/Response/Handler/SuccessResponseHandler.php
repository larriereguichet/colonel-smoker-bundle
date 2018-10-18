<?php

namespace LAG\SmokerBundle\Response\Handler;

use LAG\SmokerBundle\Exception\Exception;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class SuccessResponseHandler implements ResponseHandlerInterface
{
    public function supports(string $routeName): bool
    {
        return true;
    }

    public function handle(string $routeName, Crawler $crawler, Client $client)
    {
        if (null === $client->getResponse()) {
            throw new Exception('The client has no response. It should make a request before handle a response');
        }
        $responseCode = $client->getResponse()->getStatus();
        //dump($client->getResponse());

        if (Response::HTTP_OK !== $client->getResponse()->getStatus()) {
            throw new Exception('Excepted code 200, got '.$responseCode.' for route '.$routeName);
        }
    }
}
