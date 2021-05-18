<?php

namespace LAG\SmokerBundle\Configuration;

use JK\Configuration\Configuration;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathConfiguration extends Configuration
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define('type')
            ->required()
            ->allowedValues(['route', 'route_collection', 'url', 'url_collection'])

            ->define('options')
            ->default([])

            ->define('handlers')
            ->required()
            ->allowedTypes('array')

            ->define('data_provider')
            ->default(null)
            ->allowedTypes('string', 'null')
        ;
    }
}
