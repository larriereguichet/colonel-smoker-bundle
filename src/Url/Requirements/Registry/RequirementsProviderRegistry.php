<?php

namespace LAG\SmokerBundle\Url\Requirements\Registry;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Contracts\Requirements\Provider\RequirementsProviderInterface;

class RequirementsProviderRegistry
{
    protected $registry = [];

    /**
     * @param string                        $name
     * @param RequirementsProviderInterface $provider
     */
    public function add(string $name, RequirementsProviderInterface $provider): void
    {
        $this->registry[$name] = $provider;
    }

    /**
     * @param string $name
     *
     * @return RequirementsProviderInterface
     *
     * @throws Exception
     */
    public function get(string $name): RequirementsProviderInterface
    {
        if (!$this->has($name)) {
            throw new Exception('The requirements provider "'.$name.'" is not registered');
        }

        return $this->registry[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return key_exists($name, $this->registry);
    }

    /**
     * @return RequirementsProviderInterface[]
     */
    public function all(): array
    {
        return $this->registry;
    }
}
