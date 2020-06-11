<?php

namespace Base\HotelsDbBundle\Entity\Dictionary;


use Base\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Base\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FacilityVersion
 * @package Base\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Base\HotelsDbBundle\Repository\Dictionary\FacilityVersionRepository")
 * @ORM\Table(name="hotels_db_dictionary_facility_version", indexes={
 *     @ORM\Index(name="FACILITY_ID_IDX", columns={"facility_id"})
 * })
 */
class FacilityVersion extends AbstractFacility implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Facility", cascade={"persist"})
     * @ORM\JoinColumn(name="facility_id", referencedColumnName="id", nullable=false)
     * @var Facility
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