<?php

namespace LAG\SmokerBundle\Tests\Url;

use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Url\Url;

class UrlTest extends BaseTestCase
{
    public function testUrl()
    {
        $url = new Url('/pandas', 'panda');

        $serialized = serialize([
            'location' => '/pandas',
            'providerName' => 'panda',
        ]);
        $this->assertEquals($serialized, $url->serialize());

        $serialized = serialize([
            'location' => '/bamboos',
            'providerName' => 'bamboo',
        ]);
        $url->unserialize($serialized);
        $this->assertEquals('/bamboos', $url->getLocation());
        $this->assertEquals('bamboo', $url->getProviderName());
    }
}
