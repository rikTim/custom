<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelSPReference
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_hotel_sp_reference")
 */
class HotelSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel", inversedBy="serviceProviderReferences", fetch="EAGER")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var Hotel
     */
    protected $entity;
}