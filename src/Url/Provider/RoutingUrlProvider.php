<?php

namespace LAG\SmokerBundle\Url\Provider;

use LAG\SmokerBundle\Url\Collection\UrlCollection;
use LAG\SmokerBundle\Requirements\Provider\RequirementsProviderInterface;
use LAG\SmokerBundle\Requirements\Registry\RequirementsProviderRegistry;
use LAG\SmokerBundle\Url\Url;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\RouterInterface;

class RoutingUrlProvider implements UrlProviderInterface
{
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

    public function getCollection(): UrlCollection
    {
        $collection = new UrlCollection();

        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if (in_array($routeName, $this->excludes)) {
                continue;
            }
            $requirements = [];

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

//                    if (0 < count($requirements)) {
//                        break;
//                    }
                }

//                if (0 < count($route->getRequirements()) && count($route->getRequirements()) !== count($requirements)) {
//                    $cause = sprintf(
//                        'Invalid requirements. Expected : %s, Got %s',
//                        print_r($route->getRequirements(), true),
//                        print_r($requirements, true)
//                    );
//                    $this->ignoredUrls[$routeName] = $cause;
//
//                    continue;
//                }
            }

            try {
                //$this->router->generate($routeName, $requirements);
            } catch (MissingMandatoryParametersException $exception) {
                $this->errorUrls[$routeName] = $exception->getMessage();
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
