<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FacilityGroupSPReference
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_dictionary_facility_group_sp_reference")
 */
class FacilityGroupSPReference extends  AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\FacilityGroup", inversedBy="serviceProviderReference")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var FacilityGroup
     */
    protected $entity;
}