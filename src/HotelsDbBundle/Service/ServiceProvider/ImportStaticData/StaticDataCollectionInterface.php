<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData;


use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;


/**
 * Interface StaticDataCollectionInterface
 *
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData
 */
interface StaticDataCollectionInterface extends \Iterator, \Countable
{
    /**
     * @return string
     */
    public function getEntityClassName(): string;

    /**
     * @param ServiceProviderReferencedEntityInterface $entity
     */
    public function setParentEntity(ServiceProviderReferencedEntityInterface $entity): void;

    /**
     * @return ServiceProviderReferencedEntityInterface|null
     */
    public function getParentEntity(): ?ServiceProviderReferencedEntityInterface;

    /**
     * @return ImportDTO
     */
    public function current(): ImportDTO;

    /**
     * @return string
     */
    public function key(): string;
}