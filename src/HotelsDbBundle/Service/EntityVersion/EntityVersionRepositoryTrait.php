<?php


namespace Base\HotelsDbBundle\Service\EntityVersion;


use Base\HotelsDbBundle\Entity\AbstractAlias;

trait EntityVersionRepositoryTrait
{
    /**
     * Implemented in Doctrine\ORM\EntityRepository
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @return object|null
     */
    abstract public function findOneBy(array $criteria, array $orderBy = null);

    /**
     * @param VersionedEntityInterface $entity
     * @param AbstractAlias $responsibleAlias
     * @return EntityVersionInterface|object|null
     */
    public function findLastVersionByResponsible(VersionedEntityInterface $entity, AbstractAlias $responsibleAlias): ?EntityVersionInterface
    {
        return $this->findOneBy([
            'entity' => $entity,
            'responsibleAlias.alias' => (string)$responsibleAlias,
        ], ['id' => 'desc']);
    }
}