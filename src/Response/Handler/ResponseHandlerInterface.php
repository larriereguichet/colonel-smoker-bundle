<?php

namespace LAG\SmokerBundle\Response\Handler;

use Goutte\Client;
use LAG\SmokerBundle\Exception\Exception;
use Symfony\Component\DomCrawler\Crawler;

interface ResponseHandlerInterface
{
    public function supports(string $routeName): bool;

    /**
     * @param string  $routeName
     * @param Crawler $crawler
     * @param Client  $client
     *
     * @throws Exception
     */
    public function handle(string $routeName, Crawler $crawler, Client $client): void;
}
