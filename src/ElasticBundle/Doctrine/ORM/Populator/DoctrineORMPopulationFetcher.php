<?php

namespace Nimble\ElasticBundle\Doctrine\ORM\Populator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Nimble\ElasticBundle\Populator\PopulationFetcherInterface;

class DoctrineORMPopulationFetcher implements PopulationFetcherInterface
{
    const ROOT_ALIAS = 'e';

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var ClassMetadata
     */
    protected $entityClassMetadata;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     * @param string $entityName
     */
    public function __construct(EntityManager $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $entityManager->getRepository($entityName);
        $this->entityClassMetadata = $entityManager->getClassMetadata(
            $this->entityRepository->getClassName()
        );
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->entityRepository->createQueryBuilder(self::ROOT_ALIAS);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityCount()
    {
        $qb = $this->createQueryBuilder();

        $qb->select($qb->expr()->count(self::ROOT_ALIAS));

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchEntities($offset = 0, $limit = null)
    {
        $this->entityManager->clear(); /* TODO: Check if this gives us any value. */

        $qb = $this->createQueryBuilder();

        $qb->select(self::ROOT_ALIAS);

        foreach ($this->entityClassMetadata->getIdentifierFieldNames() as $fieldName) {
            $qb->addOrderBy(sprintf('%s.%s', self::ROOT_ALIAS, $fieldName));
        }

        $qb->setFirstResult($offset);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}