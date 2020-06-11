<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelRoomStayVersion
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelInterestPointVersionRepository")
 * @ORM\Table(name="hotels_db_hotel_interest_point_version", indexes={
 *     @ORM\Index(name="INTEREST_POINT_ID_IDX", columns={"interest_point_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelInterestPointVersion extends AbstractHotelInterestPoint implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelInterestPoint", cascade={"persist"})
     * @ORM\JoinColumn(name="interest_point_id", referencedColumnName="id", nullable=false)
     * @var HotelInterestPoint
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