<?php

namespace LAG\SmokerBundle\Url\Provider;

use LAG\SmokerBundle\Url\Collection\UrlCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface UrlProviderInterface
{
    public function getCollection(array $options = []): UrlCollection;

    public function configureOptions(OptionsResolver $resolver): void;

    /**
     * Return the provider name.
     *
     * @return string
     */
    public function getName(): string;

    public function getErrorMessages(): array;

    public function getIgnoredMessages(): array;
}
