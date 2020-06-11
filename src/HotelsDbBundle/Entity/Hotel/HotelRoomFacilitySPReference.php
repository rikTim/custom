<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelRoomSPReference
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_hotel_room_facility_sp_reference")
 */
class HotelRoomFacilitySPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoomFacility", inversedBy="serviceProviderReferences", fetch="EAGER")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var HotelRoomFacility
     */
    protected $entity;
}