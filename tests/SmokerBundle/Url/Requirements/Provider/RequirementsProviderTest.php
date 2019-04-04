<?php

namespace LAG\SmokerBundle\Tests\Url\Requirements\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use LAG\SmokerBundle\Bridge\Doctrine\ORM\RequirementsProvider\ORMRequirementsProvider;
use LAG\SmokerBundle\Contracts\DataProvider\DataProviderInterface;
use LAG\SmokerBundle\Contracts\Requirements\Mapping\MappingResolverInterface;
use LAG\SmokerBundle\Contracts\Requirements\Provider\RequirementsProviderInterface;
use LAG\SmokerBundle\Tests\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RequirementsProviderTest extends BaseTestCase
{
    public function testServiceExists(): void
    {
        $this->assertServiceExists(ORMRequirementsProvider::class);
        $this->assertServiceExists(RequirementsProviderInterface::class);
    }

    public function testSupports()
    {
        list($provider, $mappingResolver) = $this->createProvider();

        // The supports() method should use the mapping resolver to get its mapping data
        $mappingResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('the_silk_route')
            ->willReturn([
                'route' => 'the_silk_route',
                'provider' => 'default',
            ]);

        $this->assertTrue($provider->supports('the_silk_route'));
    }

    public function testSupportsWithPattern()
    {
        list($provider, $mappingResolver) = $this->createProvider();

        // The supports() method should use the mapping resolver to get its mapping data
        $mappingResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('panda_route')
            ->willReturn([]);

        $this->assertFalse($provider->supports('panda_route'));
    }

    public function testSupportsWithExclusion()
    {
        list($provider, $mappingResolver) = $this->createProvider();

        // The supports() method should use the mapping resolver to get its mapping data
        $mappingResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('panda_route')
            ->willReturn([])
        ;

        $this->assertFalse($provider->supports('panda_route'));
    }

    public function testGetName()
    {
        list($provider) = $this->createProvider();

        $this->assertEquals('default', $provider->getName());
    }

    public function testGetRequirements()
    {
        list($provider, $mappingResolver, $router, $dataProvider) = $this->createProvider();

        $mappingResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('panda_route')
            ->willReturn([
                    'provider' => 'default',
                    'excludes' => [],
                    'route' => 'panda_route',
                    'entity' => 'MyLittlePanda',
                    'requirements' => [
                        'pandaName' => 'name',
                        'bamboo' => '@green_one',
                    ],
                    'options' => [

                    ],
            ]);

        // The router should be called to get route requirements
        $route = new Route('/pandas/{pandaName}/{bamboo}', [
            'pandaName' => 'John',
        ]);
        $routeCollection = $this->createMock(RouteCollection::class);
        $routeCollection
            ->expects($this->once())
            ->method('get')
            ->with('panda_route')
            ->willReturn($route);
        $router
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($routeCollection);


        $entity = new stdClass();
        $entity->name = 'John The Panda';

        $dataProvider
            ->expects($this->once())
            ->method('getData')
            ->willReturn(new ArrayCollection([
                [$entity],
            ]));
        $requirements = $provider->getRequirementsData('panda_route');

        foreach ($requirements as $requirement) {
            $this->assertInternalType('array', $requirement);
            $this->assertArrayHasKey('pandaName', $requirement);
            $this->assertArrayHasKey('bamboo', $requirement);
            $this->assertEquals('John The Panda', $requirement['pandaName']);
            $this->assertEquals('green_one', $requirement['bamboo']);
        }
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
