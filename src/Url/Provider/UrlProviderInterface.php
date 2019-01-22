<?php

namespace LAG\SmokerBundle\Url\Provider;

use LAG\SmokerBundle\Url\Collection\UrlCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface UrlProviderInterface
{
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

    public function configure(OptionsResolver $resolver): void;

    /**
     * Return the provider name.
     *
     * @return string
     */
    public function getName(): string;
}
