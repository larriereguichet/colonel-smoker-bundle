<?php

namespace LAG\SmokerBundle\Tests\Url\Registry;

use LAG\SmokerBundle\Contracts\Url\Provider\UrlProviderInterface;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Url\Registry\UrlProviderRegistry;
use LAG\SmokerBundle\Url\UrlInfo;

class UrlProviderRegistryTest extends BaseTestCase
{
    public function testAdd()
    {
        $provider = $this->createMock(UrlProviderInterface::class);
        $provider
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('my_little_provider')
        ;

        $registry = $this->createProvider();
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

        $registry = $this->createProvider();
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

        $registry = $this->createProvider();
        $registry->add($provider);

        $this->assertEquals($provider, $registry->get('my_little_provider'));
    }

    public function testGetWithException()
    {
        $this->expectException(Exception::class);
        $provider = $this->createMock(UrlProviderInterface::class);
        $registry = $this->createProvider();
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

        $registry = $this->createProvider();
        $registry->add($provider);

        $this->assertCount(1, $registry->all());
        $this->assertEquals($provider, $registry->all()['my_little_provider']);
    }

    public function testMatch()
    {
        $urlInfo = $this->createMock(UrlInfo::class);
        $notSupportedProvider = $this->createMock(UrlProviderInterface::class);
        $notSupportedProvider
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('my_wrong_provider')
        ;
        $notSupportedProvider
            ->method('supports')
            ->with('/my-url')
            ->willReturn(false)
        ;

        $provider = $this->createMock(UrlProviderInterface::class);
        $provider
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('my_little_provider')
        ;
        $provider
            ->method('supports')
            ->with('/my-url')
            ->willReturn(true)
        ;
        $provider
            ->method('match')
            ->with('/my-url')
            ->willReturn($urlInfo)
        ;

        $registry = $this->createProvider();
        $registry->add($notSupportedProvider);
        $registry->add($provider);

        $result = $registry->match('/my-url');

        $this->assertEquals($urlInfo, $result);
    }

    /**
     * @expectedException \LAG\SmokerBundle\Exception\Exception
     */
    public function testMatchWithoutProvider()
    {
        $registry = $this->createProvider();
        $registry->match('/my-url');
    }

    private function createProvider(): UrlProviderRegistry
    {
        return new UrlProviderRegistry();
    }
}
