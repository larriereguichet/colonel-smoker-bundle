<?php

namespace LAG\SmokerBundle\Bridge\Doctrine\ORM\DataProvider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use LAG\SmokerBundle\Contracts\DataProvider\DataProviderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

class ORMDataProvider implements DataProviderInterface
{
    /**
     * @var EntityManagerInterface|EntityManager
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getData(string $class, array $options = []): Traversable
    {
        $options = $this->resolveOptions($options);

        $queryBuilder = $this
            ->entityManager
            ->getRepository($class)
            ->createQueryBuilder($options['alias'])
        ;

        foreach ($options['where'] as $parameter => $value) {
            if (is_int($parameter)) {
                $queryBuilder->andWhere($value);
            } else {
                $clause = $options['alias'].'.'.$parameter.' = :'.$parameter;
                $queryBuilder
                    ->andWhere($clause)
                    ->setParameter($parameter, $value)
                ;
            }
        }

        return $queryBuilder->getQuery()->iterate();
    }

    public function getIdentifier(string $class): array
    {
        return $this->entityManager->getClassMetadata($class)->getIdentifier();
    }

    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'alias' => 'entity',
                'requirements' => [],
                'where' => [],
            ])
            ->setAllowedTypes('alias', 'string')
            ->setAllowedTypes('requirements', 'array')
            ->setAllowedTypes('where', [
                'array',
                'string',
            ])
            ->setNormalizer('where', function (Options $options, $value) {
                // Allow the configuration "where: article.enabled" instead of
                // where:
                //    - article.enabled
                if (is_string($value)) {
                    $value = [
                        $value,
                    ];
                }

                return $value;
            })
        ;

        return $resolver->resolve($options);
    }
}
