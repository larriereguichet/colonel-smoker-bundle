<?php

namespace LAG\SmokerBundle\Bridge\Doctrine\ORM\DataProvider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
            ->setAllowedTypes('where', 'array')
        ;

        return $resolver->resolve($options);
    }
}
