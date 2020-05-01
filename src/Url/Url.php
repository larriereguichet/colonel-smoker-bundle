<?php

namespace LAG\SmokerBundle\Url;

use LAG\SmokerBundle\Exception\Exception;

class Url
{
    /**
     * @var string
     */
    protected $location;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var array
     */
    protected $options;

    /**
     * Url constructor.
     */
    public function __construct(string $location, string $providerName, array $options = [])
    {
        $this->location = $location;
        $this->providerName = $providerName;
        $this->options = $options;
    }

    public function serialize(): string
    {
        return serialize([
            'location' => $this->location,
            'providerName' => $this->providerName,
            'options' => $this->options,
        ]);
    }

    public static function deserialize(string $serialized): Url
    {
        $data = unserialize($serialized);

        return new Url(
            $data['location'],
            $data['providerName'],
            $data['options']
        );
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function hasOption(string $name): bool
    {
        return key_exists($name, $this->options);
    }

    public function getOption(string $name)
    {
        if (!$this->hasOption($name)) {
            throw new Exception('Unknown options "'.$name.'"');
        }

        return $this->options[$name];
    }
}
