<?php

namespace LAG\SmokerBundle\Url\Requirements\Registry;

use LAG\SmokerBundle\Contracts\Requirements\Provider\RequirementsProviderInterface;
use LAG\SmokerBundle\Exception\Exception;

class RequirementsProviderRegistry
{
    protected $registry = [];

    public function add(string $name, RequirementsProviderInterface $provider): void
    {
        $this->registry[$name] = $provider;
    }

    /**
     * @throws Exception
     */
    public function get(string $name): RequirementsProviderInterface
    {
        if (!$this->has($name)) {
            throw new Exception('The requirements provider "'.$name.'" is not registered');
        }

        return $this->registry[$name];
    }

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
