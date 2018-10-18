<?php

namespace LAG\SmokerBundle\Url\Provider;

use LAG\SmokerBundle\Url\Collection\UrlCollection;

interface UrlProviderInterface
{
    public function getCollection(): UrlCollection;

    public function getErrorMessages(): array;

    public function getIgnoredMessages(): array;
}
