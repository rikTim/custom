<?php


namespace Apl\HotelsDbBundle\Repository\Traits;


use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

/**
 * Trait FindExistByUniqueConstraintTrait
 *
 * @package Apl\HotelsDbBundle\Repository\Traits
 */
trait FindExistByUniqueConstraintTrait
{
    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $alias
     * @param string $indexBy The index for the from.
     *
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);

    /**
     * @return ClassMetadata
     */
    abstract protected function getClassMetadata();

    /**
     * @param $entity
     * @return mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findExistByUniqueConstraint($entity)
    {
        $classMetadata = $this->getClassMetadata();

        $qb = $this->createQueryBuilder('t');

        $exprAll = [];
        foreach ($classMetadata->table['uniqueConstraints'] as $key => $columns) {
            $expr = [];
            foreach ($columns['columns'] as $columnName) {
                $fieldName = $classMetadata->getFieldForColumn($columnName);
                $expr[] = $qb->expr()->eq('t.' . $fieldName, ':' . $key . '_' . $columnName);
                $qb->setParameter($key . '_' . $columnName, $classMetadata->getReflectionProperty($fieldName)->getValue($entity));
            }

            $exprAll[] = $qb->expr()->andX(...$expr);

        }

        $qb->orWhere(...$exprAll);
        return $qb->getQuery()->getOneOrNullResult();
    }
}