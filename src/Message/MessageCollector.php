<?php

namespace LAG\SmokerBundle\Message;

use Symfony\Component\Filesystem\Filesystem;

class MessageCollector implements MessageCollectorInterface
{
    private $errors = [];
    private $success = [];
    private $warnings = [];

    /**
     * @var Filesystem
     */
    private $fileSystem;

    private $cacheFile;

    /**
     * MessageCollector constructor.
     *
     * @param string $cacheDirectory
     */
    public function __construct(string $cacheDirectory)
    {
        $this->cacheFile = $cacheDirectory.'/smoker/smoker.messages';
        $this->fileSystem = new Filesystem();
    }

    public function initialize(): void
    {
        $this->fileSystem->dumpFile($this->cacheFile, '');
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        foreach ($this->errors as $error) {
            $this->fileSystem->appendToFile($this->cacheFile, 'ERROR='.serialize($error).PHP_EOL);
        }
        $this->errors = [];

        foreach ($this->warnings as $warning) {
            $this->fileSystem->appendToFile($this->cacheFile, 'WARNING='.serialize($warning).PHP_EOL);
        }
        $this->warnings = [];

        foreach ($this->success as $success) {
            $this->fileSystem->appendToFile($this->cacheFile, 'SUCCESS='.serialize($success).PHP_EOL);
        }
        $this->success = [];
    }

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
