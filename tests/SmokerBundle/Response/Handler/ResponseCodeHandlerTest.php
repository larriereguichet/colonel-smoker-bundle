<?php

namespace LAG\SmokerBundle\Tests\Response\Handler;

use Goutte\Client;
use LAG\SmokerBundle\Contracts\Response\Handler\ResponseHandlerInterface;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Response\Handler\ResponseCodeHandler;
use LAG\SmokerBundle\Tests\BaseTestCase;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;

class ResponseCodeHandlerTest extends BaseTestCase
{
    public function testServiceExists()
    {
        $this->assertServiceExists(ResponseCodeHandler::class);
        $this->assertServiceExists(ResponseHandlerInterface::class);
    }

    public function testSupports()
    {
        $handler = $this->createResponseHandler([
            'existent_route' => [
                'handlers' => [
                    'response_code' => '200',
                ],
            ],
            'bad_bad_route' => [],
        ]);

        $this->assertFalse($handler->supports('non_existent_route'));
        $this->assertFalse($handler->supports('bad_bad_route'));
        $this->assertTrue($handler->supports('existent_route'));
    }

    public function testHandle()
    {
        $crawler = $this->createMock(Crawler::class);
        $goutte = $this->createMock(Client::class);
        $goutte
            ->expects($this->exactly(4))
            ->method('getResponse')
            ->willReturn(new Response())
        ;

        $handler = $this->createResponseHandler([
            'existent_route' => [
                'handlers' => [
                    'response_code' => '200',
                ],
            ],
            'existent_route_too' => [
                'handlers' => [
                    'response_code' => [
                        'code' => '200'
                    ],
                ],
            ],
        ]);
        $handler->handle('existent_route', $crawler, $goutte);
        $handler->handle('existent_route_too', $crawler, $goutte);
    }

    public function testHandleExpecting302()
    {
        $crawler = $this->createMock(Crawler::class);
        $client = $this->createMock(Client::class);
        $client
            ->expects($this->exactly(2))
            ->method('getResponse')
            ->willReturn(new Response('', 302))
        ;

        $handler = $this->createResponseHandler([
            'existent_route' => [
                'handlers' => [
                    'response_code' => '302',
                ],
            ]
        ]);
        $handler->handle('existent_route', $crawler, $client);
    }

    public function testHandleInvalidStatusCode()
    {
        $crawler = $this->createMock(Crawler::class);
        $client = $this->createMock(Client::class);
        $client
            ->expects($this->exactly(2))
            ->method('getResponse')
            ->willReturn(new Response('', 302))
        ;

        $handler = $this->createResponseHandler([
            'existent_route' => [
                'handlers' => [
                    'response_code' => '200',
                ],
            ]
        ]);
        $this->assertExceptionRaised(Exception::class, function () use ($handler, $crawler, $client) {
            $handler->handle('existent_route', $crawler, $client);
        });
    }

    public function testHandleWithInvalidCrawler()
    {
        $crawler = $this->createMock(Crawler::class);
        $client = $this->createMock(Client::class);

        $handler = $this->createResponseHandler([
            'existent_route' => [
                'handlers' => [
                    'response_code' => '200',
                ],
            ]
        ]);

        $this->assertExceptionRaised(Exception::class, function () use ($handler, $crawler, $client) {
            $handler->handle('existent_route', $crawler, $client);
        });
    }

    public function testGetName()
    {
        $this->assertEquals('response_code', $this->createResponseHandler()->getName());
    }

    private function createResponseHandler(array $routes = [])
    {
        $handler = new ResponseCodeHandler($routes);

        return $handler;
    }
}
