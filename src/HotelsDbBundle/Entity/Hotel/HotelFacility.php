<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class HotelFacility
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelFacilityRepository")
 * @ORM\Table(name="hotels_db_hotel_facility", indexes={
 *     @ORM\Index(name="FACILITY_HOTEL_ID_IDX", columns={"hotel_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelFacility extends AbstractHotelFacility implements ServiceProviderReferencedEntityInterface
{
    use HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelFacilitySPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var HotelFacilitySPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel", inversedBy="facilities")
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)
     * @var Hotel|null
     */
    private $hotel;

    /**
     * HotelFacility constructor.
     */
    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
    }

    /**
     * @return ServiceProviderReferenceInterface[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
        return $this->serviceProviderReferences;
    }

    /**
     * @return Hotel|null
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel|null $hotel
     * @return HotelFacility
     */
    public function setHotel(?Hotel $hotel): HotelFacility
    {
        $this->hotel = $hotel;
        return $this;
    }
}