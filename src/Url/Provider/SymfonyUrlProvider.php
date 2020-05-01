<?php

namespace LAG\SmokerBundle\Url\Provider;

use Generator;
use LAG\SmokerBundle\Contracts\Url\Provider\UrlProviderInterface;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Url\Collection\UrlCollection;
use LAG\SmokerBundle\Url\Requirements\Registry\RequirementsProviderRegistry;
use LAG\SmokerBundle\Url\Url;
use LAG\SmokerBundle\Url\UrlInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class SymfonyUrlProvider implements UrlProviderInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var RequirementsProviderRegistry
     */
    protected $requirementsProviderRegistry;

    /**
     * @var array
     */
    protected $routes;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var array
     */
    protected $routingConfiguration;

    /**
     * SymfonyRoutingProvider constructor.
     */
    public function __construct(
        array $routingConfiguration,
        array $routes,
        array $mapping,
        RouterInterface $router,
        RequirementsProviderRegistry $requirementsProviderRegistry
    ) {
        $this->router = $router;
        $this->requirementsProviderRegistry = $requirementsProviderRegistry;
        $this->routes = $routes;
        $this->mapping = $mapping;
        $this->routingConfiguration = $routingConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'symfony';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $url): bool
    {
        $urlParts = parse_url($url);

        if (!is_array($urlParts) || !key_exists('path', $urlParts)) {
            return false;
        }
        $path = $urlParts['path'];

        try {
            $this->router->match($path);
        } catch (NoConfigurationException $exception) {
            return false;
        } catch (ResourceNotFoundException $exception) {
            return false;
        } catch (MethodNotAllowedException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function match(string $url): UrlInfo
    {
        $urlParts = parse_url($url);

        if (!is_array($urlParts) || !key_exists('path', $urlParts)) {
            throw new Exception('Can not extract the path from the url "'.$url.'"');
        }
        $path = $urlParts['path'];
        $routingInfo = $this->router->match($path);

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'scheme' => '',
                'host' => '',
                'port' => 80,
                'path' => '',
                'query' => '',
                'fragment' => '',
            ])
        ;
        $urlParts = $resolver->resolve($urlParts);

        $urlInfo = new UrlInfo(
            $urlParts['scheme'],
            $urlParts['host'],
            (int) $urlParts['port'],
            $urlParts['path'],
            $urlParts['query'],
            $urlParts['fragment'],
            $routingInfo['_route'],
            $routingInfo
        );

        return $urlInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(array $options = []): UrlCollection
    {
        $collection = new UrlCollection();
        $routes = $this->router->getRouteCollection()->all();
        $this->defineContext();

        foreach ($this->routes as $routeName => $routeOptions) {
            // The provided routes should be present in the Symfony routing
            if (!key_exists($routeName, $routes)) {
                continue;
            }
            $route = $routes[$routeName];

            // Two cases: if the route is dynamic, we should generate an url for each requirements provided by
            // requirements providers. If the route is static, one url is generated
            if ($this->hasRouteRequirements($route)) {
                $routeParametersCollection = $this->getRouteRequirements($routeName);

                foreach ($routeParametersCollection as $routeParameters) {
                    $urlParameters = [];

                    if (key_exists('_identifiers', $routeParameters)) {
                        $urlParameters['identifiers'] = $routeParameters['_identifiers'];
                        unset($routeParameters['_identifiers']);
                    }
                    // Use the absolute url parameters to preserve the route configuration, especially if an host is
                    // configured in the Symfony routing
                    $url = $this->router->generate($routeName, $routeParameters, Router::ABSOLUTE_URL);

                    $collection->add(new Url($url, $this->getName(), $urlParameters));
                }
            } else {
                $url = $this->router->generate($routeName, [], Router::ABSOLUTE_URL);
                $collection->add(new Url($url, $this->getName()));
            }
        }

        return $collection;
    }

    /**
     * Return a generator containing each set of parameters according to the providers.
     */
    protected function getRouteRequirements(string $routeName): Generator
    {
        foreach ($this->requirementsProviderRegistry->all() as $requirementsProvider) {
            if (!$requirementsProvider->supports($routeName)) {
                continue;
            }
            $requirements = $requirementsProvider->getRequirementsData($routeName);

            foreach ($requirements as $values) {
                $routeParameters = [];

                foreach ($values as $name => $value) {
                    $routeParameters[$name] = $value;
                }
                yield $routeParameters;
            }
        }
    }

    /**
     * Return true if the given route requires parameters to be generated.
     */
    protected function hasRouteRequirements(Route $route): bool
    {
        if (0 < count($route->getRequirements())) {
            return true;
        }

        if (preg_match('/\{(.*?)\}/', $route->getPath())) {
            return true;
        }

        return false;
    }

    protected function defineContext(): void
    {
        $context = $this->router->getContext();
        $context->setScheme($this->routingConfiguration['scheme']);
        $context->setHost($this->routingConfiguration['host']);
        $context->setBaseUrl($this->routingConfiguration['base_url']);

        if ('https' === $context->getScheme()) {
            $context->setHttpsPort($this->routingConfiguration['port']);
        } else {
            $context->setHttpPort($this->routingConfiguration['port']);
        }
    }
}
