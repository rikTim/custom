<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;

/**
 * Interface ServiceProviderReferencedRepositoryInterface
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver
 */
interface ServiceProviderReferencedRepositoryInterface
{
    /**
     * @return ServiceProviderReferenceInterface
     */
    public function createServiceProviderReference(): ServiceProviderReferenceInterface;

    /**
     * @param ServiceProviderAlias $alias
     * @param array $references
     * @return ServiceProviderReferenceInterface[]
     */
    public function resolveAllByServiceProviderReference(ServiceProviderAlias $alias, array $references): array;

    /**
     * @param array $fields
     * @param ServiceProviderReferencedEntityInterface|null $parentEntity
     * @return ServiceProviderReferencedEntityInterface|null
     */
    public function resolveByServiceProviderData(
        array $fields,
        ?ServiceProviderReferencedEntityInterface $parentEntity = null
    ): ?ServiceProviderReferencedEntityInterface;
}