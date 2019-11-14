<?php

namespace LAG\SmokerBundle\Url;

class UrlInfo
{
    protected $scheme = '';
    protected $host = '';
    protected $port = 80;
    protected $user = '';
    protected $pass = '';
    protected $path = '';
    protected $query = '';
    protected $fragment = '';
    protected $routeName = '';
    protected $extra = [];

    public function __construct(
        string $scheme,
        string $host,
        int $port,
        string $path,
        string $query,
        string $fragment,
        string $routeName,
        array $extra = [],
        string $user = '',
        string $pass = ''
    ) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
        $this->routeName = $routeName;
        $this->extra = $extra;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }
}
