<?php


namespace Apl\HotelsDbBundle\Entity\Location;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class CountrySPReference
 * @package Apl\HotelsDbBundle\Entity\Destination
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_location_destination_sp_reference")
 */
class DestinationSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Location\Destination", inversedBy="serviceProviderReferences", fetch="EAGER")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var Destination
     */
    protected $entity;
}