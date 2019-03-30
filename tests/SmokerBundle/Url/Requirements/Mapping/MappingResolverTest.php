<?php

namespace LAG\SmokerBundle\Tests\Url\Requirements\Mapping;

use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Url\Requirements\Mapping\MappingResolver;
use LAG\SmokerBundle\Url\Requirements\Mapping\MappingResolverInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class MappingResolverTest extends BaseTestCase
{
    public function testServiceExists()
    {
        $this->assertServiceExists(MappingResolver::class);
        $this->assertServiceExists(MappingResolverInterface::class);
    }

    public function testResolveWithEmptyMapping()
    {
        $data = $this->createResolver([])->resolve('my_little_provider', 'my_little_route');
        $this->assertInternalType('array', $data);
    }

    public function testResolve()
    {
        $data = $this->createResolver([
            'panda' => [
                'entity' => 'MyLittlePanda',
                'provider' => 'my_little_provider',
                'pattern' => 'my_little_pattern',
            ],
        ])->resolve('my_little_provider', 'my_little_route');
        $this->assertInternalType('array', $data);
    }

    public function testResolveWithoutEntity()
    {
        $this->assertExceptionRaised(MissingOptionsException::class, function () {
            $this->createResolver([
                'panda' => [
                    'provider' => 'my_little_provider',
                ],
            ])->resolve('my_little_provider', 'my_little_route');
        });
    }

    public function testResolveWithoutProvider()
    {
        $data = $this
            ->createResolver([
                'panda' => [
                    'entity' => 'MyLittlePanda',
                    'pattern' => 'my_little_pattern',
                ],
            ])
            ->resolve('default', 'my_little_route')
        ;
        $this->assertEquals([], $data);
    }

    private function createResolver(array $mapping)
    {
        return new MappingResolver($mapping);
    }
}
