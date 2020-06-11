<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Doctrine\Common\Collections\Collection;

interface ServiceProviderReferencedEntityInterface
{
    /**
     * @return ServiceProviderReferenceInterface[]|Collection
     */
    public function getServiceProviderReferences(): Collection;

    /**
     * @param ServiceProviderAlias $serviceProviderAlias
     * @return ServiceProviderReferenceInterface|null
     */
    public function getReferenceToServiceProvider(ServiceProviderAlias $serviceProviderAlias): ?ServiceProviderReferenceInterface;
}