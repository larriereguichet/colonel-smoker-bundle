<?php

namespace LAG\SmokerBundle\Url\Provider;

use LAG\SmokerBundle\Url\Collection\UrlCollection;
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
     * Return the route matching the given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function match(string $path): string;

    /**
     * Return true if the given path is supported by the provider.
     *
     * @param string $path
     *
     * @return bool
     */
    public function supports(string $path): bool;

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
