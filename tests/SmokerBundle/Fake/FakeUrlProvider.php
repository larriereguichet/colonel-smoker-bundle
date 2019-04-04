<?php

namespace LAG\SmokerBundle\Tests\Fake;

use LAG\SmokerBundle\Contracts\Url\Provider\UrlProviderInterface;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Url\Collection\UrlCollection;
use LAG\SmokerBundle\Url\UrlInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FakeUrlProvider implements UrlProviderInterface
{

    public function getCollection(array $options = []): UrlCollection
    {
        return new UrlCollection();
    }

    public function match(string $url): UrlInfo
    {
        throw new Exception();
    }

    public function supports(string $url): bool
    {
        return false;
    }

    public function configure(OptionsResolver $resolver): void
    {
    }

    public function getName(): string
    {
        return 'fake';
    }
}
