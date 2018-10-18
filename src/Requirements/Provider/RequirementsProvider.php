<?php

namespace LAG\SmokerBundle\Requirements\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;

class RequirementsProvider implements RequirementsProviderInterface
{
    protected $symfonyMapping = [
        '_twig_error_test' => [
            'code' => 500,
        ],
        'hwi_oauth_connect_service' => [
            'service' => 'hwi_oauth_connect_service',
        ],
        'hwi_oauth_service_redirect' => [
            'service' => 'google',
        ],
        'hwi_oauth_connect_registration' => [
            'key' => 'mykey',
        ],

        'liip_imagine_filter_runtime' => [
            'filter' => 'test',
            'path' => 'test',
            'hash' => 'test',
        ]
    ];

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * RequirementsProvider constructor.
     *
     * @param array           $mapping
     * @param RouterInterface $router
     * @param Registry        $registry
     */
    public function __construct(array $mapping, RouterInterface $router, Registry $registry)
    {
        $this->mapping = $mapping;
        $this->router = $router;
        $this->registry = $registry;
    }

    public function supports(string $routeName): bool
    {

        if (key_exists($routeName, $this->symfonyMapping)) {
            return true;
        }

        return null !== $this->findMapping($routeName);
    }

    /**
     * @param string $routeName
     *
     * @return \Traversable
     *
     * @throws \Exception
     */
    public function getRequirements(string $routeName): \Traversable
    {
        if (key_exists($routeName, $this->symfonyMapping)) {
            yield $this->symfonyMapping[$routeName];

            return;
        }
        $route = $this
            ->router
            ->getRouteCollection()
            ->get($routeName)
        ;
        $configuredRequirements = $route->getRequirements();
        $mapping = $this->findMapping($routeName);

        /** @var EntityRepository $repository */
        $repository = $this->registry->getRepository($mapping['entity']);
        $entities = $repository
            ->createQueryBuilder('entity')
            ->getQuery()
            ->iterate()
        ;
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($entities as $row) {
            $entity = $row[0];
            $values = [];

            if (0 === count($configuredRequirements)) {
                $matches = [];
                preg_match_all('/{(.*?)}/', $route->getPath(), $matches);

                $configuredRequirements = array_flip($matches[1]);
            }

            foreach ($configuredRequirements as $name => $requirement) {
                if (!key_exists($name, $mapping['requirements'])) {
                    throw new \Exception(sprintf(
                        'The requirement "%s" for the route "%s" is not provided',
                        $name,
                        $routeName
                    ));
                }
                $property = $mapping['requirements'][$name];

                if ('@' === substr($property, 0, 1)) {
                    $values[$name] = $property;
                } else {
                    $values[$name] = $accessor->getValue($entity, $property);
                }
            }

            yield $values;
        }
    }

    protected function resolveRouteMapping(array $data)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'route' => null,
                'pattern' => null,
                'requirements' => [],
                'excludes' => [],
            ])
            ->setRequired([
                'entity'
            ])
            ->setAllowedTypes('route', [
                'string',
                'null',
            ])
            ->setAllowedTypes('pattern', [
                'string',
                'null',
            ])
            ->setAllowedTypes('entity', 'string')
            ->setAllowedTypes('requirements', 'array')
            ->setNormalizer('route', function (Options $options, $value) {
                if (null === $value && null === $options->offsetGet('pattern')) {
                    throw new InvalidOptionsException('A pattern or a route should be provided');
                }

                return $value;
            });

        return $resolver->resolve($data);
    }

    protected function findMapping(string $routeName)
    {
        foreach ($this->mapping as $mappingData) {
            $mappingData = $this->resolveRouteMapping($mappingData);

            if (in_array($routeName, $mappingData['excludes'])) {
                return null;
            }

            if ($routeName === $mappingData['route']) {
                return $mappingData;
            }

            if (false !== strpos($routeName, $mappingData['pattern'])) {
                return $mappingData;
            }
        }

        return null;
    }
}
