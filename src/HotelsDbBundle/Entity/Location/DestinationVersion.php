<?php


namespace Apl\HotelsDbBundle\Entity\Location;


use Apl\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class DestinationVersion
 * @package Apl\HotelsDbBundle\Entity\Location
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Location\DestinationVersionRepository")
 * @ORM\Table(name="hotels_db_location_destination_version", indexes={
 *     @ORM\Index(name="DESTINATION_ID_IDX", columns={"destination_id"})
 * })
 */
class DestinationVersion extends AbstractDestination implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Location\Destination", cascade={"persist"})
     * @ORM\JoinColumn(name="destination_id", referencedColumnName="id", nullable=false)
     * @var Destination
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