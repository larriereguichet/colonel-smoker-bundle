<?php

namespace LAG\SmokerBundle\Message;

interface MessageCollectorInterface
{
    /**
     * Initialize the messages cache file.
     */
    public function initialize(): void;

    /**
     * Add a new error message, and optional error code and exception.
     */
    public function addError(string $url, string $message, int $code = 500, \Exception $exception = null): void;

    /**
     * Add a new success message.
     */
    public function addSuccess(string $url, string $message, int $code): void;

    /**
     * Add a new warning message.
     */
    public function addWarning(string $url, string $message): void;

    /**
     * Return the list of collected errors.
     */
    public function getErrors(): array;

    /**
     * Return the list of collected success.
     */
    public function getSuccess(): array;

    /**
     * Return the list of collected warnings.
     */
    public function getWarnings(): array;

    /**
     * Flush the messages into the messages cache file.
     */
    public function flush(): void;

    public function read(): array;
}
