<?php

namespace LAG\SmokerBundle\Contracts\Requirements\Mapping;

interface MappingResolverInterface
{
    public function resolve(string $routeName, bool $filterMappingData = true): array;
}
