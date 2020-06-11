<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelFacilitySPReference
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_hotel_phone_sp_reference")
 */
class HotelPhoneSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelPhone", inversedBy="serviceProviderReferences", fetch="EAGER")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var HotelPhone
     */
    protected $entity;
}