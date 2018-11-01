<?php

namespace LAG\SmokerBundle\Message;

class MessageCollector implements MessageCollectorInterface
{
    private $errors = [];
    private $success = [];
    private $warnings = [];

    /**
     * @inheritdoc
     */
    public function addError(string $message, int $code = 500, \Exception $exception = null): void
    {
        $error = [
            'message' => $message,
            'code' => $code,
        ];

        if (null !== $exception) {
            $error['exception'] = $exception;
        }
        $this->errors[] = $error;
    }

    /**
     * @inheritdoc
     */
    public function addSuccess(string $message): void
    {
        $this->success[] = $message;
    }

    /**
     * @inheritdoc
     */
    public function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritdoc
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    /**
     * @inheritdoc
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
