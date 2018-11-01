<?php

namespace LAG\SmokerBundle\Url\Registry;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Url\Provider\UrlProviderInterface;

class UrlProviderRegistry
{
    protected $registry = [];

    /**
     * Add a new url provider to the registry. If a provider with the same name is already registered, an exception will
     * be thrown.
     *
     * @param UrlProviderInterface $provider
     *
     * @throws Exception
     */
    public function add(UrlProviderInterface $provider): void
    {
        if ($this->has($provider->getName())) {
            throw new Exception('The provider "'.$provider->getName().'" is already registered');
        }
        $this->registry[$provider->getName()] = $provider;
    }

    /**
     * Return an url provider matching the given name. If none exists, an exception will be thrown?
     *
     * @param string $name
     *
     * @return UrlProviderInterface
     *
     * @throws Exception
     */
    public function get(string $name): UrlProviderInterface
    {
        if (!$this->has($name)) {
            throw new Exception('The provider "'.$name.'" is not registered');
        }

        return $this->registry[$name];
    }

    /**
     * Return true if a provider with the given name is registered in the registry.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->registry);
    }

    /**
     * @return UrlProviderInterface[]
     */
    public function all(): array
    {
        return $this->registry;
    }
}
