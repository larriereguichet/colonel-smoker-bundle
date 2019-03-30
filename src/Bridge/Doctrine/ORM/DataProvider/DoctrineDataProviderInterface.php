<?php

namespace LAG\SmokerBundle\Bridge\Doctrine\ORM\DataProvider;

use Traversable;

interface DoctrineDataProviderInterface
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
}
