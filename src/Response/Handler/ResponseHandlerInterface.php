<?php

namespace LAG\SmokerBundle\Response\Handler;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

interface ResponseHandlerInterface
{
    public function supports(string $routeName): bool;

    public function handle(string $routeName, Crawler $crawler, Client $client);
}
