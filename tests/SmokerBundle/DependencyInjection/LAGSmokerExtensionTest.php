<?php

namespace LAG\SmokerBundle\Tests\DependencyInjection;

use LAG\SmokerBundle\DependencyInjection\LAGSmokerExtension;
use LAG\SmokerBundle\Tests\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class LAGSmokerExtensionTest extends BaseTestCase
{
    public function testGetAlias()
    {
        $extension = $this->createExtension();

        $this->assertEquals('lag_smoker', $extension->getAlias());
    }

    public function testLoad()
    {
        $config = Yaml::parse(file_get_contents(__DIR__.'/../Resources/lag_smoker.yaml'));

        $extension = $this->createExtension();
        $builder = new ContainerBuilder();

        $extension->load([
            'lag_smoker' => $config['lag_smoker'],
        ], $builder);

        $this->assertEquals([
            'scheme' => 'http',
            'host' => '127.0.0.1',
            'port' => '8000',
            'base_url' => null,
        ], $builder->getParameter('lag_smoker.routing'));

    }

    private function createExtension(): LAGSmokerExtension
    {
        return new LAGSmokerExtension();
    }
}
