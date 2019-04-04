<?php

namespace LAG\SmokerBundle\Url;

class UrlInfo
{
    public $scheme = '';
    public $host = '';
    public $port = 80;
    public $user = '';
    public $pass = '';
    public $path = '';
    public $query = '';
    public $fragment = '';
    public $routeName = '';
    public $extra = [];
}
