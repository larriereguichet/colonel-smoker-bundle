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
     *
     * @param string          $url
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $exception
     */
    public function addError(string $url, string $message, int $code = 500, \Exception $exception = null): void;

    /**
     * Add a new success message.
     *
     * @param string $url
     * @param string $message
     * @param int    $code
     */
    public function addSuccess(string $url, string $message, int $code): void;

    /**
     * Add a new warning message.
     *
     * @param string $url
     * @param string $message
     */
    public function addWarning(string $url, string $message): void;

    /**
     * Return the list of collected errors.
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Return the list of collected success.
     *
     * @return array
     */
    public function getSuccess(): array;

    /**
     * Return the list of collected warnings.
     *
     * @return array
     */
    public function getWarnings(): array;

    /**
     * Flush the messages into the messages cache file.
     */
    public function flush(): void;

    public function read(): array;
}
