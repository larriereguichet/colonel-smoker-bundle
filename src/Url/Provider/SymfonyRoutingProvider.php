<?php

namespace LAG\SmokerBundle\Url\Provider;

use Generator;
use LAG\SmokerBundle\Url\Collection\UrlCollection;
use LAG\SmokerBundle\Url\Requirements\Registry\RequirementsProviderRegistry;
use LAG\SmokerBundle\Url\Url;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRoutingProvider implements UrlProviderInterface
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
     * SymfonyRoutingProvider constructor.
     *
     * @param array                        $routes
     * @param array                        $mapping
     * @param RouterInterface              $router
     * @param RequirementsProviderRegistry $requirementsProviderRegistry
     */
    public function __construct(
        array $routes,
        array $mapping,
        RouterInterface $router,
        RequirementsProviderRegistry $requirementsProviderRegistry
    ) {
        $this->router = $router;
        $this->requirementsProviderRegistry = $requirementsProviderRegistry;
        $this->routes = $routes;
        $this->mapping = $mapping;
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
    public function supports(string $path): bool
    {
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
    public function match(string $path): string
    {
        $pathInfo = $this->router->match($path);

        return $pathInfo['_route'];
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
                    // Use the absolute url parameters to preserve the route configuration, especially if an host is
                    // configured in the Symfony routing
                    $url = $this->router->generate($routeName, $routeParameters, Router::ABSOLUTE_URL);
                    $collection->add(new Url($url, $this->getName()));
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
     *
     * @param string $routeName
     *
     * @return Generator
     */
    protected function getRouteRequirements(string $routeName): Generator
    {
        foreach ($this->requirementsProviderRegistry->all() as $requirementsProvider) {
            if (!$requirementsProvider->supports($routeName)) {
                continue;
            }
            $requirements = $requirementsProvider->getRequirements($routeName);

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
     *
     * @param Route $route
     *
     * @return bool
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
}
