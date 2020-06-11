<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FacilitySPReference
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_dictionary_facility_sp_reference")
 */
class FacilitySPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Facility", inversedBy="serviceProviderReferences")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var Facility
     */
    protected $entity;
}