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
            'options' => [],
        ]);
        $this->assertEquals($serialized, $url->serialize());

        $serialized = serialize([
            'location' => '/bamboos',
            'providerName' => 'bamboo',
            'options' => [
                'an_option' => 'a value',
            ],
        ]);

        $url = Url::deserialize($serialized);
        $this->assertEquals('/bamboos', $url->getLocation());
        $this->assertEquals('bamboo', $url->getProviderName());
        $this->assertEquals('a value', $url->getOption('an_option'));
    }
}
