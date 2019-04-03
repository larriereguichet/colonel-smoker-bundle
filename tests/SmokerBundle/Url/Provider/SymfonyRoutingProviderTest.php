<?php

namespace LAG\SmokerBundle\Tests\Url\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Url\Provider\SymfonyUrlProvider;
use LAG\SmokerBundle\Url\Requirements\Provider\RequirementsProviderInterface;
use LAG\SmokerBundle\Url\Requirements\Registry\RequirementsProviderRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRoutingProviderTest extends BaseTestCase
{
    public function testSupports()
    {
        list($provider, $router) = $this->createProvider();

        // The provider should use the Symfony routing to match the given path
        $router
            ->expects($this->once())
            ->method('match')
            ->with('/forest/bamboos')
            ->willReturn(true)
        ;
        $this->assertTrue($provider->supports('/forest/bamboos'));
    }

    public function testSupportsWithNoConfigurationException()
    {
        list($provider, $router) = $this->createProvider();

        // The provider should use the Symfony routing to match the given path
        $router
            ->expects($this->once())
            ->method('match')
            ->with('/forest/bamboos')
            ->willThrowException(new NoConfigurationException())
        ;
        $this->assertFalse($provider->supports('/forest/bamboos'));
    }

    public function testSupportsWithMethodNotAllowedException()
    {
        list($provider, $router) = $this->createProvider();

        // The provider should use the Symfony routing to match the given path
        $router
            ->expects($this->once())
            ->method('match')
            ->with('/forest/bamboos')
            ->willThrowException(new MethodNotAllowedException([]))
        ;
        $this->assertFalse($provider->supports('/forest/bamboos'));
    }

    public function testSupportsWithResourceNotFoundException()
    {
        list($provider, $router) = $this->createProvider();

        // The provider should use the Symfony routing to match the given path
        $router
            ->expects($this->once())
            ->method('match')
            ->with('/forest/bamboos')
            ->willThrowException(new ResourceNotFoundException())
        ;
        $this->assertFalse($provider->supports('/forest/bamboos'));
    }

    public function testMatch()
    {
        list($provider, $router) = $this->createProvider();

        $router
            ->expects($this->once())
            ->method('match')
            ->with('/forest/bamboos')
            ->willReturn([
                '_route' => 'panda_route',
            ])
        ;

        $this->assertEquals('panda_route', $provider->match('/forest/bamboos'));

    }

    public function testConfigure()
    {
        list($provider) = $this->createProvider();

        $resolver =$this->createMock(OptionsResolver::class);

        $provider->configure($resolver);
        $this->assertTrue(true);
    }

    public function testGetCollection()
    {
        list($provider, $router) = $this->createProvider([
            'scheme' => 'http',
            'host' => '127.0.0.1',
            'port' => '8000',
            'base_url' => null,
        ],[
            'panda_route' => [
                'provider' => 'symfony',
            ],
            'unhandled_route' => [
                'provider' => 'an_other_provider',
            ],
        ]);

        // The provider should generate urls using the route collection from Symfony routing
        $collection = $this->createMock(RouteCollection::class);
        $collection
            ->expects($this->once())
            ->method('all')
            ->willReturn([
                'panda_route' => new Route('/forest/bamboos'),
            ])
        ;

        $requestContext = new RequestContext();
        $router
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($collection)
        ;
        $router
            ->expects($this->once())
            ->method('generate')
            ->with('panda_route', [], 0)
            ->willReturn('/forest/bamboos')
        ;
        $router
            ->expects($this->once())
            ->method('getContext')
            ->willReturn($requestContext)
        ;

        $urls = $provider->getCollection();

        $this->assertCount(1, $urls->all());
    }

    public function testGetCollectionWithRequirements()
    {
        list($provider, $router, $registry) = $this->createProvider([
            'scheme' => 'http',
            'host' => '127.0.0.1',
            'port' => '8000',
            'base_url' => null,
        ], [
            'panda_route' => [
                'provider' => 'symfony',
            ],
            'unhandled_route' => [
                'provider' => 'an_other_provider',
            ],
        ]);

        // The provider should generate urls using the route collection from Symfony routing
        $collection = $this->createMock(RouteCollection::class);
        $collection
            ->expects($this->once())
            ->method('all')
            ->willReturn([
                'panda_route' => new Route('/forest/bamboos/{pandaName}', [
                    'pandaName' => 'John',
                ], [
                    'pandaName',
                ]),
            ])
        ;
        // The url provider should call requirements providers to provide requirements when generating urls
        $requirementProvider = $this->createMock(RequirementsProviderInterface::class);
        $requirementProvider
            ->expects($this->once())
            ->method('supports')
            ->with('panda_route')
            ->willReturn(true)
        ;
        $requirementProvider
            ->expects($this->once())
            ->method('getRequirements')
            ->willReturn(new ArrayCollection([
                'panda' => [
                    'pandaName' => 'John The Panda',
                ],
            ]))
        ;
        $registry
            ->expects($this->once())
            ->method('all')
            ->willReturn([
                $requirementProvider,
            ])
        ;

        $requestContext = new RequestContext();
        $router
            ->expects($this->once())
            ->method('getContext')
            ->willReturn($requestContext)
        ;
        $router
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($collection)
        ;
        $router
            ->expects($this->once())
            ->method('generate')
            ->with('panda_route')
            ->willReturn('/forest/bamboos')
        ;

        $urls = $provider->getCollection();

        $this->assertCount(1, $urls->all());
    }

    /**
     * @param array $routingConfiguration
     * @param array $routes
     * @param array $mapping
     *
     * @return MockObject[]|SymfonyUrlProvider[]
     */
    private function createProvider(array $routingConfiguration = [], array $routes = [], array $mapping = [])
    {
        $router = $this->createMock(RouterInterface::class);
        $registry = $this->createMock(RequirementsProviderRegistry::class);

        $provider = new SymfonyUrlProvider($routingConfiguration, $routes, $mapping, $router, $registry);

        return [
            $provider,
            $router,
            $registry,
        ];
    }
}
