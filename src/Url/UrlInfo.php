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

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPass(): string
    {
        return $this->pass;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }
}
