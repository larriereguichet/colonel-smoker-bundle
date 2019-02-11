<?php

namespace LAG\SmokerBundle\Tests\Url\Collection;

use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Url\Collection\UrlCollection;
use LAG\SmokerBundle\Url\Url;

class UrlCollectionTest extends BaseTestCase
{
    public function testAdd()
    {
        $collection = $this->createCollection();
        $collection->add(new Url('http://bamboo.com/pandas', 'my_little_provider'));

        $urls = $collection->all();
        $this->assertCount(1, $urls);

        $url = $urls[0];
        $this->assertEquals('http://bamboo.com/pandas', $url->getLocation());
        $this->assertEquals('my_little_provider', $url->getProviderName());
    }

    public function testCount()
    {
        $collection = $this->createCollection();
        $collection->add(new Url('http://bamboo.com/pandas', 'my_little_provider'));

        $this->assertEquals($collection->count(), count($collection->all()));
    }

    private function createCollection()
    {
        $collection = new UrlCollection();

        return $collection;
    }
}
