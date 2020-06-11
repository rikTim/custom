<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SegmentVersion
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\SegmentVersionRepository")
 * @ORM\Table(name="hotels_db_dictionary_segment_version",indexes={
 *     @ORM\Index(name="SEGMENT_ID_IDX", columns={"segment_id"})
 * })
 */
class SegmentVersion extends AbstractSegment implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Segment", cascade={"persist"})
     * @ORM\JoinColumn(name="segment_id", referencedColumnName="id", nullable=false)
     * @var Segment
     */
    protected $entity;

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_MERGE)
        );
    }
}