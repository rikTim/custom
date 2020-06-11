<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ImageTypeVersion
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\ImageTypeVersionRepository")
 * @ORM\Table(name="hotels_db_dictionary_image_type_version", indexes={
 *     @ORM\Index(name="IMAGE_TYPE_ID_IDX", columns={"image_type_id"})
 * })
 */
class ImageTypeVersion extends AbstractImageType implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\ImageType", cascade={"persist"})
     * @ORM\JoinColumn(name="image_type_id", referencedColumnName="id", nullable=false)
     * @var ImageType
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