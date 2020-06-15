<?php

namespace LAG\SmokerBundle\Contracts\Url\Provider;

use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Url\Collection\UrlCollection;
use LAG\SmokerBundle\Url\UrlInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface UrlProviderInterface
{
    /**
     * Return a collection of urls. Options can be passed to the provider.
     */
    public function getCollection(array $options = []): UrlCollection;

    /**
     * Return the route matching the given url. Throws an exception if the url can not be matched. The "supports()"
     * method should be called before matching an url.
     *
     * @throws Exception
     */
    public function match(string $url): UrlInfo;

    /**
     * Return true if the given url is supported by the provider.
     */
    public function supports(string $url): bool;

    /**
     * Configure options to be passed to the getCollection() method.
     */
    public function configure(OptionsResolver $resolver): void;

    /**
     * Return the provider name.
     */
    public function getName(): string;
}
