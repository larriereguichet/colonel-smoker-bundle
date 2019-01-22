<?php

namespace LAG\SmokerBundle\Url;

class Url implements \Serializable
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
     * Url constructor.
     *
     * @param string $location
     * @param string $providerName
     */
    public function __construct(string $location, string $providerName)
    {
        $this->location = $location;
        $this->providerName = $providerName;
    }

    public function serialize(): string
    {
        return serialize([
            'location' => $this->location,
            'providerName' => $this->providerName,
        ]);
    }

    /**
     * Constructs the object.
     *
     * @see https://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->location = $data['location'];
        $this->providerName = $data['providerName'];
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }
}
