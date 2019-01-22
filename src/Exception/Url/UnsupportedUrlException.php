<?php

namespace LAG\SmokerBundle\Exception\Url;

use LAG\SmokerBundle\Exception\Exception;

class UnsupportedUrlException extends Exception
{
    public function __construct(string $routeName, string $providerName)
    {
        $message = sprintf(
            'The route "%s" is not supported by the provider "%s". Dit you forget to call the supports() method ?',
            $routeName,
            $providerName
        );

        parent::__construct($message);
    }
}
