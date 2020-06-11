<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Trait HasServiceProviderReferencesTrait
 * @package Apl\HotelsDbBundle\Entity\Traits
 */
trait HasServiceProviderReferencesTrait
{
    /**
     * @return ServiceProviderReferenceInterface[]|Collection
     */
    abstract public function getServiceProviderReferences(): Collection;

    /**
     * @param ServiceProviderAlias $serviceProviderAlias
     * @return ServiceProviderReferenceInterface|null
     */
    public function getReferenceToServiceProvider(ServiceProviderAlias $serviceProviderAlias): ?ServiceProviderReferenceInterface
    {
        foreach ($this->getServiceProviderReferences() as $mapping) {
            if (($alias = $mapping->getAlias()) && $alias->isEqual($serviceProviderAlias)) {
                return $mapping;
            }
        }

        return null;
    }
}