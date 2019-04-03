<?php

namespace LAG\SmokerBundle\Url\Requirements\Mapping;

interface MappingResolverInterface
{
    public function resolve(string $routeName, bool $filterMappingData = true): array;
}
