<?php

namespace LAG\SmokerBundle\Tests;

use LAG\SmokerBundle\DependencyInjection\CompilerPass\ResponseHandlerCompilerPass;
use LAG\SmokerBundle\DependencyInjection\CompilerPass\UrlProviderCompilerPass;
use LAG\SmokerBundle\DependencyInjection\LAGSmokerExtension;
use LAG\SmokerBundle\LAGSmokerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LAGSmokerBundleTest extends BaseTestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new LAGSmokerBundle();

        $this->assertEquals(new LAGSmokerExtension(), $bundle->getContainerExtension());
    }

    public function testBuild()
    {
        $container = new ContainerBuilder();
        $bundle = new LAGSmokerBundle();

        $bundle->build($container);

        ($container->getCompilerPassConfig()->getPasses());
        $urlPass = false;
        $responsePass = false;

        foreach ($container->getCompilerPassConfig()->getPasses() as $compilerPass) {
            if ($compilerPass instanceof UrlProviderCompilerPass) {
                $urlPass = true;
            }
            if ($compilerPass instanceof ResponseHandlerCompilerPass) {
                $responsePass = true;
            }
        }
        $this->assertTrue($urlPass);
        $this->assertTrue($responsePass);
    }
}
