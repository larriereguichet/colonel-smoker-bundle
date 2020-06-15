<?php

namespace LAG\SmokerBundle\Response\Registry;

use LAG\SmokerBundle\Contracts\Response\Handler\ResponseHandlerInterface;
use LAG\SmokerBundle\Exception\Exception;

class ResponseHandlerRegistry
{
    protected $registry = [];

    public function add(string $name, ResponseHandlerInterface $handler): void
    {
        $this->registry[$name] = $handler;
    }

    /**
     * @throws Exception
     */
    public function get(string $name): ResponseHandlerInterface
    {
        if (!$this->has($name)) {
            throw new Exception('The response handler "'.$name.'" is not registered');
        }

        return $this->registry[$name];
    }

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
