<?php

namespace LAG\SmokerBundle\Contracts\DataProvider;

use Traversable;

interface DataProviderInterface
{
    /**
     * Return a traversable object with all entities of the repository associated to the given entity class.
     */
    public function getData(string $class, array $options = []): Traversable;

    public function getIdentifier(string $class): array;
}
