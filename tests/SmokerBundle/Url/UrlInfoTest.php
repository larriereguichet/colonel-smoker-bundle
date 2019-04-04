<?php

namespace LAG\SmokerBundle\Tests\Url;

use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Url\UrlInfo;

class UrlInfoTest extends BaseTestCase
{
    public function testUrlInfo()
    {
        $urlInfo = new UrlInfo(
            'https',
            'localhost',
            80,
            '/',
            'query',
            'fragments',
            'my_little_route',
            [
                'test' => true,
            ],
            'my_user',
            'my_password'
        );

        $this->assertEquals('https', $urlInfo->getScheme());
        $this->assertEquals('localhost', $urlInfo->getHost());
        $this->assertEquals(80, $urlInfo->getPort());
        $this->assertEquals('/', $urlInfo->getPath());
        $this->assertEquals('query', $urlInfo->getQuery());
        $this->assertEquals('fragments', $urlInfo->getFragment());
        $this->assertEquals('my_little_route', $urlInfo->getRouteName());
        $this->assertEquals([
            'test' => true,
        ], $urlInfo->getExtra());
        $this->assertEquals('my_user', $urlInfo->getUser());
        $this->assertEquals('my_password', $urlInfo->getPass());
    }
}
