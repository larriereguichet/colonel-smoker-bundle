<?php

namespace LAG\SmokerBundle\Url\Requirements\Provider;

use LAG\SmokerBundle\Exception\Exception;
use Traversable;

interface RequirementsProviderInterface
{
    /**
     * Return true if the given route name is supported by the provider.
     *
     * @param string $routeName
     *
     * @return bool
     */
    public function supports(string $routeName): bool;

    /**
     * Return a list of requirements for the given route name. The requirements should be like [
     *   'parameterName' => 'parameterValue',
     * ];
     * The provider could throw an exception if the routing configuration does not match with the mapping configured by
     * the user.
     *
     * @param string $routeName
     * @param array  $options
     *
     * @throws Exception
     *
     * @return Traversable
     */
    public function getRequirements(string $routeName, array $options = []): Traversable;

    /**
     * Return the provider name.
     *
     * @return string
     */
    public function getName(): string;
}
