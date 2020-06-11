<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Apl\HotelsDbBundle\Entity\EmbeddableServiceProviderReference;


/**
 * Trait HasEmbeddedServiceProviderReference
 *
 * @package Apl\HotelsDbBundle\Entity\Traits
 */
trait HasEmbeddedServiceProviderReference
{
    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\EmbeddableServiceProviderReference", columnPrefix=false)
     * @var EmbeddableServiceProviderReference
     */
    private $serviceProviderReference;

    /**
     * @return EmbeddableServiceProviderReference|null
     */
    public function getServiceProviderReference(): ?EmbeddableServiceProviderReference
    {
        return $this->serviceProviderReference;
    }

    /**
     * @param EmbeddableServiceProviderReference $serviceProviderReference
     * @return self
     */
    public function setServiceProviderReference(EmbeddableServiceProviderReference $serviceProviderReference): self
    {
        $this->serviceProviderReference = $serviceProviderReference;
        return $this;
    }
}