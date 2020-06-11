<?php


namespace Apl\HotelsDbBundle\Service\EntityVersion;


use Apl\HotelsDbBundle\Entity\AbstractAlias;

interface EntityVersionRepositoryInterface
{
    /**
     * @param VersionedEntityInterface $entity
     * @param AbstractAlias $responsibleAlias
     * @return EntityVersionInterface|object|null
     */
    public function findLastVersionByResponsible(VersionedEntityInterface $entity, AbstractAlias $responsibleAlias): ?EntityVersionInterface;
}