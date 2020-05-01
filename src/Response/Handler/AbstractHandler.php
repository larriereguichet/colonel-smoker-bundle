<?php

namespace LAG\SmokerBundle\Response\Handler;

use LAG\SmokerBundle\Contracts\Response\Handler\ResponseHandlerInterface;

abstract class AbstractHandler implements ResponseHandlerInterface
{
    /**
     * @var array
     */
    protected $configuration = [];

    abstract public function getName(): string;

    /**
     * ResponseCodeHandler constructor.
     */
    public function __construct(array $configuration = [])
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $routeName): bool
    {
        if (!key_exists($routeName, $this->configuration)) {
            return false;
        }

        if (!key_exists('handlers', $this->configuration[$routeName])) {
            return false;
        }

        return key_exists($this->getName(), $this->configuration[$routeName]['handlers']);
    }

    protected function getConfiguration(string $routeName): array
    {
        return $this->configuration[$routeName]['handlers'][$this->getName()];
    }

    protected function getMappingName(string $routeName): ?string
    {
        if (!key_exists('mapping', $this->configuration[$routeName])) {
            return null;
        }

        return $this->configuration[$routeName]['mapping'];
    }
}
