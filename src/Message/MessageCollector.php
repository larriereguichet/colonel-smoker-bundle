<?php

namespace LAG\SmokerBundle\Message;

use Symfony\Component\Filesystem\Filesystem;

class MessageCollector implements MessageCollectorInterface
{
    private $errors = [];
    private $success = [];
    private $warnings = [];

    const LINE_SEPARATOR = '###END###'.PHP_EOL;

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

    public function read(): array
    {
        $messages = [
            'success' => [],
            'errors' => [],
            'warnings' => [],
        ];
        $content = file_get_contents($this->cacheFile);
        $data = explode(self::LINE_SEPARATOR, $content);

        foreach ($data as $messageData) {
            $messageData = explode('=', $messageData);

            if ('ERROR' === $messageData[0]) {
                $messages['errors'][] = unserialize($messageData[1]);
            }

            if ('WARNING' === $messageData[0]) {
                $messages['warnings'][] = unserialize($messageData[1]);
            }

            if ('SUCCESS' === $messageData[0]) {
                $messages['success'][] = unserialize($messageData[1]);
            }
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        foreach ($this->errors as $error) {
            $this->fileSystem->appendToFile($this->cacheFile, 'ERROR='.serialize($error).self::LINE_SEPARATOR);
        }
        $this->errors = [];

        foreach ($this->warnings as $warning) {
            $this->fileSystem->appendToFile($this->cacheFile, 'WARNING='.serialize($warning).self::LINE_SEPARATOR);
        }
        $this->warnings = [];

        foreach ($this->success as $success) {
            $this->fileSystem->appendToFile($this->cacheFile, 'SUCCESS='.serialize($success).self::LINE_SEPARATOR);
        }
        $this->success = [];
    }

    /**
     * {@inheritdoc}
     */
    public function addError(string $url, string $message, int $code = 500, \Exception $exception = null): void
    {
        $error = [
            'url' => $url,
            'message' => $message,
            'code' => $code,
        ];

        if (null !== $exception) {
            $error['stacktrace'] = $exception->getTraceAsString();
        }
        $this->errors[] = $error;
    }

    /**
     * {@inheritdoc}
     */
    public function addSuccess(string $url, string $message, int $code = 200): void
    {
        $this->success[] = [
            'url' => $url,
            'message' => $message,
            'code' => $code,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function addWarning(string $url, string $message, int $code = 200): void
    {
        $this->warnings[] = [
            'url' => $url,
            'message' => $message,
            'code' => $code,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
