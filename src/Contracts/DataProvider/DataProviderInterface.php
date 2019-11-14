<?php

namespace LAG\SmokerBundle\Contracts\DataProvider;

use Traversable;

interface DataProviderInterface
{
    /**
     * Return a traversable object with all entities of the repository associated to the given entity class.
     *
     * @param string $class
     * @param array  $options
     *
     * @return Traversable
     */
    public function getData(string $class, array $options = []): Traversable;

    public function getIdentifier(string $class): array;
}
