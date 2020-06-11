<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;

use Apl\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PromotionVersion
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\PromotionVersionRepository")
 * @ORM\Table(name="hotels_db_dictionary_promotion_version", indexes={
 *     @ORM\Index(name="PROMOTION_ID_IDX", columns={"promotion_id"})
 * })
 */
class PromotionVersion extends AbstractPromotion implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Promotion", cascade={"persist"})
     * @ORM\JoinColumn(name="promotion_id", referencedColumnName="id", nullable=false)
     * @var Promotion
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