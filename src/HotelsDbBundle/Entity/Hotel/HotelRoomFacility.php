<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class HotelRoomFacility
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelRoomFacilityRepository")
 * @ORM\Table(name="hotels_db_hotel_room_facility", indexes={
 *     @ORM\Index(name="FACILITY_ROOM_ID_IDX", columns={"room_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelRoomFacility extends AbstractHotelFacility implements ServiceProviderReferencedEntityInterface
{
    use HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoomFacilitySPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var HotelRoomFacilitySPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\ManyToOne(targetEntity="HotelRoom", inversedBy="facilities")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=false)
     * @var HotelRoom|null
     */
    private $room;

    /**
     * HotelRoomFacility constructor.
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
     * @return HotelRoom|null
     */
    public function getRoom(): ?HotelRoom
    {
        return $this->room;
    }

    /**
     * @param HotelRoom|null $room
     * @return HotelRoomFacility
     */
    public function setRoom(?HotelRoom $room): HotelRoomFacility
    {
        $this->room = $room;
        return $this;
    }
}