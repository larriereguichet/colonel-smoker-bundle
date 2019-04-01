<?php

namespace LAG\SmokerBundle\Bridge\Doctrine\ORM\DataProvider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

class DoctrineDataProvider implements DoctrineDataProviderInterface
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

        foreach ($options['where'] as $clause) {
            $queryBuilder->andWhere($clause);
        }

        return $queryBuilder->getQuery()->iterate();
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
                'string'
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
