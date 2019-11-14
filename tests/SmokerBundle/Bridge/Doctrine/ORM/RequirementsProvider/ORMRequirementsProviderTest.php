<?php

namespace LAG\SmokerBundle\Tests\Bridge\Doctrine\ORM\RequirementsProvider;

use LAG\SmokerBundle\Bridge\Doctrine\ORM\RequirementsProvider\ORMRequirementsProvider;
use LAG\SmokerBundle\Contracts\DataProvider\DataProviderInterface;
use LAG\SmokerBundle\Contracts\Requirements\Mapping\MappingResolverInterface;
use LAG\SmokerBundle\Tests\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class ORMRequirementsProviderTest extends BaseTestCase
{
    public function testGetRequirementsWithoutRequirements()
    {
        list($provider,,$router) = $this->createProvider();

        $collection = $this->createMock(RouteCollection::class);
        $collection
            ->expects($this->once())
            ->method('get')
            ->with('my_route')
            ->willReturn(new Route('/a-little-url'))
        ;

        $router
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($collection)
        ;

        $requirements = $provider->getRequirements('my_route');
    }

    public function testGetRequirements()
    {
        list($provider,,$router) = $this->createProvider();

        $collection = $this->createMock(RouteCollection::class);
        $collection
            ->expects($this->once())
            ->method('get')
            ->with('my_route')
            ->willReturn(new Route('/a-little-url/{id}', [], [
                'id' => '*',
            ]))
        ;

        $router
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($collection)
        ;

        $requirements = $provider->getRequirements('my_route');
    }

    /**
     * @return ORMRequirementsProvider[]|MockObject[]
     */
    private function createProvider(): array
    {
        $mappingResolver = $this->createMock(MappingResolverInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $dataProvider = $this->createMock(DataProviderInterface::class);
        $provider = new ORMRequirementsProvider(
            $mappingResolver,
            $router,
            $dataProvider
        );

        return [
            $provider,
            $mappingResolver,
            $router,
            $dataProvider,
        ];
    }
}
