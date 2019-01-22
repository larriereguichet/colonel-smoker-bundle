<?php

namespace LAG\SmokerBundle\Url\Requirements\Provider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Exception\Url\UnsupportedUrlException;
use LAG\SmokerBundle\Url\Requirements\Mapping\MappingResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Traversable;

class RequirementsProvider implements RequirementsProviderInterface
{
    private $name = 'default';

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var MappingResolverInterface
     */
    protected $mappingResolver;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * RequirementsProvider constructor.
     *
     * @param MappingResolverInterface $mappingResolver
     * @param RouterInterface          $router
     * @param EntityManagerInterface   $entityManager
     */
    public function __construct(
        MappingResolverInterface $mappingResolver,
        RouterInterface $router,
        EntityManagerInterface $entityManager
    ) {
        $this->mappingResolver = $mappingResolver;
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $routeName): bool
    {
        return null !== $this->findMapping($routeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirements(string $routeName, array $options = []): Traversable
    {
        $mapping = $this->findMapping($routeName);

        if (null === $mapping) {
            throw new UnsupportedUrlException($routeName, $this->name);
        }
        $route = $this
            ->router
            ->getRouteCollection()
            ->get($routeName)
        ;
        $configuredRequirements = $route->getRequirements();

        $repository = $this->entityManager->getRepository($mapping['entity']);
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
                    throw new Exception(sprintf(
                        'The requirement "%s" for the route "%s" is not provided',
                        $name,
                        $routeName
                    ));
                }
                $property = $mapping['requirements'][$name];

                if ('@' === substr($property, 0, 1)) {
                    $values[$name] = substr($property, 1);
                } else {
                    $values[$name] = $accessor->getValue($entity, $property);
                }
            }

            yield $values;
        }
    }

    protected function findMapping(string $routeName): ?array
    {
        $mapping = $this->mappingResolver->resolve($this->name, $routeName);

        foreach ($mapping as $mappingData) {
            if (in_array($routeName, $mappingData['excludes'])) {
                continue;
            }

            if (key_exists('route', $mappingData) && $routeName === $mappingData['route']) {
                return $mappingData;
            }

            if (key_exists('pattern', $mappingData) && false !== strpos($routeName, $mappingData['pattern'])) {
                return $mappingData;
            }
        }

        return null;
    }
}
