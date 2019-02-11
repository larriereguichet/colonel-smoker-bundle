<?php

namespace LAG\SmokerBundle\Response\Handler;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

interface ResponseHandlerInterface
{
    /**
     * Return true if the given route name is supported by the response handler.
     *
     * @param string $routeName
     *
     * @return bool
     */
    public function supports(string $routeName): bool;

    /**
     * Handle the response according to the given route name. If response data does not match
     * expectations, an exception will be thrown.
     *
     * @param string  $routeName
     * @param Crawler $crawler
     * @param Client  $client
     * @param array   $options
     */
    public function handle(string $routeName, Crawler $crawler, Client $client, array $options = []): void;

    /**
     * Return the unique name of the response handler.
     *
     * @return string
     */
    public function getName(): string;
}
