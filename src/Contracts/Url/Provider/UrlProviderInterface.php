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
     *
     * @param array $options
     *
     * @return UrlCollection
     */
    public function getCollection(array $options = []): UrlCollection;

    /**
     * Return the route matching the given url. Throws an exception if the url can not be matched. The "supports()"
     * method should be called before matching an url.
     *
     * @param string $url
     *
     * @throws Exception
     *
     * @return UrlInfo
     */
    public function match(string $url): UrlInfo;

    /**
     * Return true if the given url is supported by the provider.
     *
     * @param string $url
     *
     * @return bool
     */
    public function supports(string $url): bool;

    /**
     * Configure options to be passed to the getCollection() method.
     *
     * @param OptionsResolver $resolver
     */
    public function configure(OptionsResolver $resolver): void;

    /**
     * Return the provider name.
     *
     * @return string
     */
    public function getName(): string;
}
