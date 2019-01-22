<?php

namespace LAG\SmokerBundle\Url\Requirements\Mapping;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This class resolve the mapping configuration for a given provider. The mapping of url requirements are
 * based on entities and not providers themselves, so we must loop on each mapping to find data related
 * to the given provider.
 */
class MappingResolver implements MappingResolverInterface
{
    /**
     * @var array
     */
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function resolve(string $providerName, string $routeName): array
    {
        $providerMapping = [];

        foreach ($this->mapping as $name => $map) {
            $map = $this->resolveMap($map);

            if ($map['provider'] !== $providerName) {
                continue;
            }
            $providerMapping[$name] = $map;
        }

        return $providerMapping;
    }

    protected function resolveMap(array $map)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'route' => null,
                'pattern' => null,
                'requirements' => [],
                'excludes' => [],
                'provider' => 'default',
            ])
            ->setRequired([
                'entity',
            ])
            ->setAllowedTypes('route', [
                'string',
                'null',
            ])
            ->setAllowedTypes('pattern', [
                'string',
                'null',
            ])
            ->setAllowedTypes('provider', 'string')
            ->setAllowedTypes('entity', 'string')
            ->setAllowedTypes('requirements', 'array')
            ->setNormalizer('route', function (Options $options, $value) {
                if (null === $value && null === $options->offsetGet('pattern')) {
                    throw new InvalidOptionsException('A pattern or a route should be provided');
                }

                return $value;
            });

        return $resolver->resolve($map);
    }
}
