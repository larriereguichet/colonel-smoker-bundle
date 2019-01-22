<?php

namespace LAG\SmokerBundle\Tests\Url\Requirements\Registry;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Url\Requirements\Provider\RequirementsProviderInterface;
use LAG\SmokerBundle\Url\Requirements\Registry\RequirementsProviderRegistry;

class RequirementsProviderRegistryTest extends BaseTestCase
{
    public function testServiceExists()
    {
        $this->assertServiceExists(RequirementsProviderRegistry::class);
    }

    public function testRegistry()
    {
        $provider = $this->createMock(RequirementsProviderInterface::class);

        $registry = new RequirementsProviderRegistry();
        $registry->add('default', $provider);

        $this->assertTrue($registry->has('default'));
        $this->assertEquals($provider, $registry->get('default'));
        $this->assertEquals($provider, $registry->all()['default']);

        $this->assertExceptionRaised(Exception::class, function () use ($registry) {
            $registry->get('whatever');
        });
    }
}
