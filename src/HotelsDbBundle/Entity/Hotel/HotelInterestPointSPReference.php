<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelFacilitySPReference
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_hotel_interest_point_sp_reference")
 */
class HotelInterestPointSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelInterestPoint", inversedBy="serviceProviderReferences", fetch="EAGER")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var HotelInterestPoint
     */
    protected $entity;
}