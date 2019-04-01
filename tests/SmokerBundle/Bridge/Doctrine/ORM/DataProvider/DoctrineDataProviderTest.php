<?php

namespace LAG\SmokerBundle\Tests\Bridge\Doctrine\ORM\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use LAG\SmokerBundle\Bridge\Doctrine\ORM\DataProvider\DoctrineDataProvider;
use LAG\SmokerBundle\Tests\BaseTestCase;
use LAG\SmokerBundle\Tests\Fake\FakeQuery;

class DoctrineDataProviderTest extends BaseTestCase
{
    public function testGetData()
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(FakeQuery::class);

        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with('MyClass')
            ->willReturn($repository)
        ;
        $repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('my_little_alias')
            ->willReturn($queryBuilder)
        ;
        $queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with('my_little_alias.enabled = true')
        ;
        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($query)
        ;
        $query
            ->expects($this->once())
            ->method('iterate')
            ->willReturn($this->createGenerator([
                'a_thing',
            ]))
        ;


        $provider = new DoctrineDataProvider($entityManager);

        $data = $provider->getData('MyClass', [
            'alias' => 'my_little_alias',
            'where' => 'my_little_alias.enabled = true'
        ]);
    }

    private function createGenerator(array $data)
    {
        foreach ($data as $datum) {
            yield $data;
        }
    }

}
