<?php

namespace LAG\SmokerBundle\Response\Registry;

use LAG\SmokerBundle\Contracts\Response\Handler\ResponseHandlerInterface;
use LAG\SmokerBundle\Exception\Exception;

class ResponseHandlerRegistry
{
    protected $registry = [];

    /**
     * @param string                   $name
     * @param ResponseHandlerInterface $handler
     */
    public function add(string $name, ResponseHandlerInterface $handler): void
    {
        $this->registry[$name] = $handler;
    }

    /**
     * @param string $name
     *
     * @return ResponseHandlerInterface
     *
     * @throws Exception
     */
    public function get(string $name): ResponseHandlerInterface
    {
        if (!$this->has($name)) {
            throw new Exception('The response handler "'.$name.'" is not registered');
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
     * @return ResponseHandlerInterface[]
     */
    public function all(): array
    {
        return $this->registry;
    }
}
