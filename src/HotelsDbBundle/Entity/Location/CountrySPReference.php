<?php


namespace Apl\HotelsDbBundle\Entity\Location;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CountrySPReference
 * @package Apl\HotelsDbBundle\Entity\Destination
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_location_country_sp_reference")
 */
class CountrySPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Location\Country", inversedBy="serviceProviderReferences")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var Country
     */
    protected $entity;
}