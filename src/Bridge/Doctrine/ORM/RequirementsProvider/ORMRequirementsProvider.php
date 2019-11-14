<?php

namespace LAG\SmokerBundle\Bridge\Doctrine\ORM\RequirementsProvider;

use LAG\SmokerBundle\Contracts\DataProvider\DataProviderInterface;
use LAG\SmokerBundle\Contracts\Requirements\Mapping\MappingResolverInterface;
use LAG\SmokerBundle\Contracts\Requirements\Provider\RequirementsProviderInterface;
use LAG\SmokerBundle\Exception\Exception;
use LAG\SmokerBundle\Exception\Url\UnsupportedUrlException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Traversable;

class ORMRequirementsProvider implements RequirementsProviderInterface
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
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * RequirementsProvider constructor.
     *
     * @param MappingResolverInterface $mappingResolver
     * @param RouterInterface          $router
     * @param DataProviderInterface    $dataProvider
     */
    public function __construct(
        MappingResolverInterface $mappingResolver,
        RouterInterface $router,
        DataProviderInterface $dataProvider
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
        $mapping = $this->mappingResolver->resolve($routeName);

        if ([] === $mapping) {
            return false;
        }

        return $this->name === $mapping['provider'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirementsData(string $routeName, array $options = []): Traversable
    {
        $options = $this->resolveOptions($options);
        $mapping = $this->mappingResolver->resolve($routeName);

        if ([] === $mapping) {
            throw new UnsupportedUrlException($routeName, $this->name);
        }

        if ($this->name !== $mapping['provider']) {
            throw new Exception('The provider "'.$this->name.'" does not support the route "'.$routeName.'"');
        }

        if (!key_exists('where', $mapping['options'])) {
            $mapping['options']['where'] = [];
        }

        if (!is_array($mapping['options']['where'])) {
            $mapping['options']['where'] = [
                $mapping['options']['where'],
            ];
        }
        // Allow optional dynamic criteria to find specific entities
        $mapping['options']['where'] = array_merge($mapping['options']['where'], $options['where']);

        $entities = $this
            ->dataProvider
            ->getData($mapping['entity'], $mapping['options'])
        ;

        foreach ($entities as $row) {
            $values = $this->processRow($row, $routeName, $mapping);

            yield $values;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirements(string $routeName): array
    {
        $route = $this
            ->router
            ->getRouteCollection()
            ->get($routeName)
        ;
        $requirements = $route->getRequirements();

        if (0 === count($requirements)) {
            $matches = [];
            preg_match_all('/{(.*?)}/', $route->getPath(), $matches);

            $requirements = array_flip($matches[1]);
        }

        return $requirements;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataProvider(): DataProviderInterface
    {
        return $this->dataProvider;
    }

    /**
     * @param array  $row
     * @param string $routeName
     * @param array  $mapping
     *
     * @return array
     *
     * @throws Exception
     */
    private function processRow(array $row, string $routeName, array $mapping): array
    {
        $requirements = $this->getRequirements($routeName);
        $entity = $row[0];
        $values = [];
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($requirements as $name => $requirement) {
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
        $identifiers = $this->dataProvider->getIdentifier($mapping['entity']);
        $values['_identifiers'] = [];

        foreach ($identifiers as $identifier) {
            $values['_identifiers'][$identifier] = $accessor->getValue($entity, $identifier);
        }

        return $values;
    }

    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'where' => [],
            ])
            ->setAllowedTypes('where', 'array')
        ;

        return $resolver->resolve($options);
    }
}
