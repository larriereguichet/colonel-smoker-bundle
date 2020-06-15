<?php

namespace LAG\SmokerBundle\Contracts\Response\Handler;

use Goutte\Client;
use LAG\SmokerBundle\Exception\Exception;
use Symfony\Component\DomCrawler\Crawler;

interface ResponseHandlerInterface
{
    /**
     * Return true if the given route name is supported by the response handler.
     */
    public function supports(string $routeName): bool;

    /**
     * Handle the response according to the given route name. If response data does not match
     * expectations, an exception will be thrown. An exception will be thrown if the dom crawler is not
     * initialized before passing it to the response handler.
     *
     * @throws Exception
     */
    public function handle(string $routeName, Crawler $crawler, Client $client, array $options = []): void;

    /**
     * Return the unique name of the response handler.
     */
    public function getName(): string;
}
