<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FacilityGroupVersion
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\FacilityGroupVersionRepository")
 * @ORM\Table(name="hotels_db_dictionary_facility_group_version", indexes={
 *     @ORM\Index(name="FACILITY_GROUP_ID_IDX", columns={"facility_group_id"})
 * })
 */
class FacilityGroupVersion extends  AbstractFacilityGroup implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\FacilityGroup", cascade={"persist"})
     * @ORM\JoinColumn(name="facility_group_id", referencedColumnName="id", nullable=false)
     * @var FacilityGroup
     */
    protected $entity;

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_MERGE)
        );
    }
}