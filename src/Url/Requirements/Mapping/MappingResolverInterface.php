<?php

namespace LAG\SmokerBundle\Url\Requirements\Mapping;

interface MappingResolverInterface
{
    public function resolve(string $providerName, string $routeName): array;
}
