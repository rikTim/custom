<?php


namespace Base\HotelsDbBundle\Entity\Hotel;


use Base\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelRoomSPReference
 * @package Base\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_hotel_room_sp_reference")
 */
class HotelRoomSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Base\HotelsDbBundle\Entity\Hotel\HotelRoom", inversedBy="serviceProviderReferences", fetch="EAGER")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var HotelRoom
     */
    protected $entity;
}