<?php

namespace LAG\SmokerBundle;

use LAG\SmokerBundle\DependencyInjection\CompilerPass\ResponseHandlerCompilerPass;
use LAG\SmokerBundle\DependencyInjection\CompilerPass\UrlProviderCompilerPass;
use LAG\SmokerBundle\DependencyInjection\LAGSmokerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LAGSmokerBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new LAGSmokerExtension();
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ResponseHandlerCompilerPass());
        $container->addCompilerPass(new UrlProviderCompilerPass());
    }
}
