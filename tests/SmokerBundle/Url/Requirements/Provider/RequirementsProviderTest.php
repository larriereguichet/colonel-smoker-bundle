<?php

namespace LAG\SmokerBundle\Tests\Url\Requirements\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Tests\Fake\FakeQuery;
use LAG\SmokerBundle\Url\Requirements\Mapping\MappingResolverInterface;
use LAG\SmokerBundle\Url\Requirements\Provider\RequirementsProvider;
use LAG\SmokerBundle\Url\Requirements\Provider\RequirementsProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RequirementsProviderTest extends BaseTestCase
{
    public function testServiceExists(): void
    {
        $this->assertServiceExists(RequirementsProvider::class);
        $this->assertServiceExists(RequirementsProviderInterface::class);
    }

    public function testSupports()
    {
        list($provider, $mappingResolver) = $this->createProvider();

        // The supports() method should use the mapping resolver to get its mapping data
        $mappingResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('default', 'the_silk_route')
            ->willReturn([
                'silk_mapping' => [
                    'excludes' => [],
                    'route' => 'the_silk_route',
                ],
            ])
        ;

        $this->assertTrue($provider->supports('the_silk_route'));
    }

    public function testSupportsWithPattern()
    {
        list($provider, $mappingResolver) = $this->createProvider();

        // The supports() method should use the mapping resolver to get its mapping data
        $mappingResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('default', 'panda_route')
            ->willReturn([
                'bad_bamboo' => [
                    'excludes' => [
                        'panda_route',
                    ],
                    'route' => 'wrong',
                ],
                'good_bamboo' => [
                    'excludes' => [],
                    'pattern' => 'panda',
                ],
            ])
        ;

        $this->assertTrue($provider->supports('panda_route'));
    }

    public function testSupportsWithExclusion()
    {
        list($provider, $mappingResolver) = $this->createProvider();

        // The supports() method should use the mapping resolver to get its mapping data
        $mappingResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('default', 'panda_route')
            ->willReturn([
                'bad_bamboo' => [
                    'excludes' => [
                        'panda_route',
                    ],
                    'route' => 'wrong',
                ],
            ])
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
        list($provider, $mappingResolver, $router, $entityManager) = $this->createProvider();

        $mappingResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('default', 'panda_route')
            ->willReturn([
                'panda' => [
                    'excludes' => [],
                    'route' => 'panda_route',
                    'entity' => 'MyLittlePanda',
                    'requirements' => [
                        'pandaName' => 'name',
                        'bamboo' => '@green_one',
                    ]
                ],
            ])
        ;

        // The router should be called to get route requirements
        $route = new Route('/pandas/{pandaName}/{bamboo}', [
            'pandaName' => 'John',
        ]);
        $routeCollection = $this->createMock(RouteCollection::class);
        $routeCollection
            ->expects($this->once())
            ->method('get')
            ->with('panda_route')
            ->willReturn($route)
        ;
        $router
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($routeCollection)
        ;


        $entity = new \stdClass();
        $entity->name = 'John The Panda';
        $query = $this->createMock(FakeQuery::class);
        $query
            ->expects($this->once())
            ->method('iterate')
            ->willReturn([
                0 => [
                    $entity,
                ],
            ])
        ;
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($query)
        ;
        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('entity')
            ->willReturn($queryBuilder)
        ;
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository)
        ;
        $requirements = $provider->getRequirements('panda_route');

        foreach ($requirements as $requirement) {
            $this->assertInternalType('array', $requirement);
            $this->assertArrayHasKey('pandaName', $requirement);
            $this->assertArrayHasKey('bamboo', $requirement);
            $this->assertEquals('John The Panda', $requirement['pandaName']);
            $this->assertEquals('green_one', $requirement['bamboo']);
        }
    }

    /**
     * @return RequirementsProvider[]|MockObject[]
     */
    private function createProvider(): array
    {
        $mappingResolver = $this->createMock(MappingResolverInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $provider = new RequirementsProvider(
            $mappingResolver,
            $router,
            $entityManager
        );

        return [
            $provider,
            $mappingResolver,
            $router,
            $entityManager,
        ];
    }
}
