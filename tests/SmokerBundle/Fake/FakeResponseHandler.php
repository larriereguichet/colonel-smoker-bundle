<?php

namespace LAG\SmokerBundle\Tests\Fake;

use Goutte\Client;
use LAG\SmokerBundle\Contracts\Response\Handler\ResponseHandlerInterface;
use Symfony\Component\DomCrawler\Crawler;

class FakeResponseHandler implements ResponseHandlerInterface
{
    public function supports(string $routeName): bool
    {
        return true;
    }

    public function handle(string $routeName, Crawler $crawler, Client $client, array $options = []): void
    {
    }

    public function getName(): string
    {
        return 'fake';
    }
}
