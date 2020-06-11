<?php


namespace Apl\HotelsDbBundle\Repository\Traits;


use Apl\HotelsDbBundle\Entity\AbstractAlias;

/**
 * Trait ResolvedByAliasTrait
 * @package Apl\HotelsDbBundle\Repository\Traits
 */
trait FindByAliasTrait
{
    /**
     * Implemented in Doctrine\ORM\EntityRepository
     * @param array $criteria
     * @param array|null $orderBy
     * @return object|null
     */
    abstract public function findOneBy(array $criteria, array $orderBy = null);

    /**
     * @param AbstractAlias $alias
     * @return object|null
     */
    public function resolveByAlias(AbstractAlias $alias)
    {
        return $this->findOneBy(['alias.alias' => (string)$alias]);
    }
}