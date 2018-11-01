<?php

namespace LAG\SmokerBundle\Url\Provider;

use LAG\SmokerBundle\Url\Collection\UrlCollection;
use LAG\SmokerBundle\Requirements\Registry\RequirementsProviderRegistry;
use LAG\SmokerBundle\Url\Url;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRoutingProvider implements UrlProviderInterface
{
    const STRATEGY_ALL = 'all';
    const STRATEGY_INCLUSIVE = 'inclusive';
    const STRATEGY_EXCLUSIVE = 'exclusive';

    /**
     * @var RouterInterface
     */
    protected $router;

    protected $ignoredUrls = [];

    protected $errorUrls = [];

    protected $excludes = [
        // Symfony profiler
        '_wdt',
        '_profiler_search_results',
        '_profiler',
        '_profiler_router',
        '_profiler_exception',
        '_profiler_exception_css',

        // LiipImagineBundle
        'liip_imagine_filter_runtime',
    ];

    /**
     * @var RequirementsProviderRegistry
     */
    protected $registry;

    public function __construct(RouterInterface $router, RequirementsProviderRegistry $registry)
    {
        $this->router = $router;
        $this->registry = $registry;
    }

    public function getName(): string
    {
        return 'symfony';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'strategy' => self::STRATEGY_ALL,
                'routes' => [],
            ])
            ->setAllowedValues('strategy', [
                self::STRATEGY_ALL,
                self::STRATEGY_INCLUSIVE,
                self::STRATEGY_EXCLUSIVE,
            ])
            ->setAllowedTypes('routes', [
                'array',
                'null',
            ])
            ->setNormalizer('routes', function (Options $options, $value) {
                if (self::STRATEGY_ALL === $options->offsetGet('strategy') && 0 !== count($value)) {
                    throw new InvalidOptionsException(
                        'The routes parameters should not be configured if the "ALL" strategy is configured'
                    );
                }

                if (self::STRATEGY_INCLUSIVE === $options->offsetGet('strategy') && null === $value) {
                    throw new InvalidOptionsException(
                        'The routes parameters should be configured if the "INCLUSIVE" strategy is configured'
                    );
                }

                if (self::STRATEGY_EXCLUSIVE === $options->offsetGet('strategy') && null === $value) {
                    throw new InvalidOptionsException(
                        'The routes parameters should be configured if the "EXCLUSIVE" strategy is configured'
                    );
                }

                return $value;
            });
    }

    public function getCollection(array $options = []): UrlCollection
    {
        $collection = new UrlCollection();

        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if (self::STRATEGY_INCLUSIVE === $options['strategy'] && !in_array($routeName, $options['routes'])) {
                continue;
            }

            if (self::STRATEGY_EXCLUSIVE === $options['strategy'] && in_array($routeName, $options['routes'])) {
                continue;
            }

            if (in_array($routeName, $this->excludes)) {
                continue;
            }

            if (0 < count($route->getRequirements()) || preg_match('/\{(.*?)\}/', $route->getPath())) {

                foreach ($this->registry->all() as $requirementsProvider) {

                    if (!$requirementsProvider->supports($routeName)) {
                        continue;
                    }
                    $requirements = $requirementsProvider->getRequirements($routeName);

                    foreach ($requirements as $values) {
                        $routeParameters = [];

                        foreach ($values as $name => $value) {
                            $routeParameters[$name] = $value;
                        }
                        $url = $this->router->generate($routeName, $routeParameters);
                        $collection->add(new Url($url, 'routing'));
                    }
                }
            } else {
                $url = $this->router->generate($routeName);
                $collection->add(new Url($url, 'routing'));
            }
        }

        return $collection;
    }

    public function getErrorMessages(): array
    {
        return $this->errorUrls;
    }

    public function getIgnoredMessages(): array
    {
        return $this->ignoredUrls;
    }
}
