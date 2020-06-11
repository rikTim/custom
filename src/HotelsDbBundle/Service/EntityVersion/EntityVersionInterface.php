<?php


namespace Apl\HotelsDbBundle\Service\EntityVersion;


use Apl\HotelsDbBundle\Entity\AbstractAlias;

/**
 * Interface EntityVersionInterface
 *
 * @package Apl\HotelsDbBundle\Service\EntityVersion
 */
interface EntityVersionInterface
{
    /**
     * @return VersionedEntityInterface|null
     */
    public function getEntity() : ?VersionedEntityInterface;

    /**
     * @param VersionedEntityInterface $entity
     * @return $this
     */
    public function setEntity(VersionedEntityInterface $entity): EntityVersionInterface;

    /**
     * @return AbstractAlias|null
     */
    public function getResponsibleAlias(): ?AbstractAlias;

    /**
     * @param AbstractAlias $alias
     * @return $this
     */
    public function setResponsibleAlias(AbstractAlias $alias): EntityVersionInterface;
}