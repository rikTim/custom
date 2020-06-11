<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;
use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FacilityTypologySPReference
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_dictionary_facility_typology_sp_reference")
 */
class FacilityTypologySPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\FacilityTypology",inversedBy="serviceProviderReferences")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var FacilityTypology
     */
    protected $entity;
}