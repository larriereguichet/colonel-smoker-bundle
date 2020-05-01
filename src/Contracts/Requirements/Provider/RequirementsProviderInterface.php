<?php

namespace LAG\SmokerBundle\Contracts\Requirements\Provider;

use LAG\SmokerBundle\Contracts\DataProvider\DataProviderInterface;
use LAG\SmokerBundle\Exception\Exception;
use Traversable;

interface RequirementsProviderInterface
{
    /**
     * Return true if the given route name is supported by the provider.
     */
    public function supports(string $routeName): bool;

    /**
     * Return a list of requirements for the given route name. The requirements should be like [
     *   'parameterName' => 'parameterValue',
     * ];
     * The provider could throw an exception if the routing configuration does not match with the mapping configured by
     * the user.
     *
     * @throws Exception
     */
    public function getRequirementsData(string $routeName, array $options = []): Traversable;

    public function getRequirements(string $routeName): array;

    public function getDataProvider(): DataProviderInterface;

    /**
     * Return the provider name.
     */
    public function getName(): string;
}
