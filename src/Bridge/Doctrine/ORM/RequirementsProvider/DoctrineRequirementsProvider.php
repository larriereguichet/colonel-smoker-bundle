<?php

namespace LAG\SmokerBundle\Bridge\Doctrine\ORM\RequirementsProvider;

use LAG\SmokerBundle\Bridge\Doctrine\ORM\DataProvider\DoctrineDataProviderInterface;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Exception\Url\UnsupportedUrlException;
use LAG\SmokerBundle\Url\Requirements\Mapping\MappingResolverInterface;
use LAG\SmokerBundle\Url\Requirements\Provider\RequirementsProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Traversable;

class DoctrineRequirementsProvider implements RequirementsProviderInterface
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
     * @var DoctrineDataProviderInterface
     */
    protected $dataProvider;

    /**
     * RequirementsProvider constructor.
     *
     * @param MappingResolverInterface      $mappingResolver
     * @param RouterInterface               $router
     * @param DoctrineDataProviderInterface $dataProvider
     */
    public function __construct(
        MappingResolverInterface $mappingResolver,
        RouterInterface $router,
        DoctrineDataProviderInterface $dataProvider
    ) {
        $this->mappingResolver = $mappingResolver;
        $this->router = $router;
        $this->dataProvider = $dataProvider;
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
        $mapping = $this->mappingResolver->resolve($this->name, $routeName);

        if ([] === $mapping) {
            return false;
        }

        return $this->name === $mapping['provider'];
    }


    /**
     * {@inheritdoc}
     */
    public function getRequirements(string $routeName, array $options = []): Traversable
    {
        $mapping = $this->mappingResolver->resolve($this->name, $routeName);

        if ([] === $mapping) {
            throw new UnsupportedUrlException($routeName, $this->name);
        }
        $entities = $this
            ->dataProvider
            ->getData($mapping['entity'], $mapping['options'])
        ;
        $route = $this
            ->router
            ->getRouteCollection()
            ->get($routeName)
        ;
        $configuredRequirements = $route->getRequirements();
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
}
