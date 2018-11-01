<?php

namespace LAG\SmokerBundle\Tests\Url\Provider;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Url\Provider\UrlProviderInterface;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class UrlProviderRegistryTest extends TestCase
{
    public function testAdd()
    {
        $provider = $this->createMock(UrlProviderInterface::class);
        $provider
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('my_little_provider')
        ;

        $registry = new UrlProviderRegistry();
        $registry->add($provider);

        $this->assertEquals($provider, $registry->get('my_little_provider'));
    }

    public function testAddWithException()
    {
        $this->expectException(Exception::class);
        $provider = $this->createMock(UrlProviderInterface::class);
        $provider
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('my_little_provider')
        ;

        $registry = new UrlProviderRegistry();
        $registry->add($provider);
        $registry->add($provider);
    }

    public function testGet()
    {
        $provider = $this->createMock(UrlProviderInterface::class);
        $provider
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('my_little_provider')
        ;

        $registry = new UrlProviderRegistry();
        $registry->add($provider);

        $this->assertEquals($provider, $registry->get('my_little_provider'));
    }

    public function testGetWithException()
    {
        $this->expectException(Exception::class);
        $provider = $this->createMock(UrlProviderInterface::class);
        $registry = new UrlProviderRegistry();

        $this->assertEquals($provider, $registry->get('my_little_provider'));
    }

    public function testAll()
    {
        $provider = $this->createMock(UrlProviderInterface::class);
        $provider
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('my_little_provider')
        ;

        $registry = new UrlProviderRegistry();
        $registry->add($provider);

        $this->assertCount(1, $registry->all());
        $this->assertEquals($provider, $registry->all()['my_little_provider']);
    }
}
