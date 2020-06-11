<?php

namespace Base\HotelsDbBundle\Entity\Dictionary;


use Base\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Base\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FacilityTypologyVersion
 * @package Base\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Base\HotelsDbBundle\Repository\Dictionary\FacilityTypologyVersionRepository")
 * @ORM\Table(name="hotels_db_dictionary_facility_typology_version", indexes={
 *     @ORM\Index(name="FACILITY_TYPOLOGY_ID_IDX", columns={"facility_typology_id"})
 * })
 */
class FacilityTypologyVersion extends AbstractFacilityTypology implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Base\HotelsDbBundle\Entity\Dictionary\FacilityTypology", cascade={"persist"})
     * @ORM\JoinColumn(name="facility_typology_id", referencedColumnName="id", nullable=false)
     * @var FacilityTypology
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