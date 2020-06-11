<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Dictionary\Facility;
use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasOrderTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractHotelInterestPoint
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\MappedSuperclass()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="FACILITY_ID_IDX", columns={"facility_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractHotelInterestPoint implements TranslatableObjectInterface, HasSetterMappingInterface, HasGetterMappingInterface
{
    private const TRANSLATABLE_NAME = 'name';

    use HasIntegerIdTrait,
        HasOrderTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Facility")
     * @ORM\JoinColumn(name="facility_id", referencedColumnName="id", nullable=false)
     * @var Facility|null
     */
    private $facility;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     * @var int|null
     */
    private $distance;

    /**
     * @inheritdoc
     */
    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableString::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new SetterAttribute('facility'),
            ScalarHydrator::getIntegerAttribute('order'),
            ScalarHydrator::getIntegerAttribute('distance')
        );
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            EqualComparator::attributeFactory('facility'),
            new GetterAttribute('order'),
            new GetterAttribute('distance'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility|null $facility
     * @return AbstractHotelInterestPoint
     */
    public function setFacility(?Facility $facility): AbstractHotelInterestPoint
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDistance(): ?int
    {
        return $this->distance;
    }

    /**
     * @param int|null $distance
     * @return AbstractHotelInterestPoint
     */
    public function setDistance(?int $distance): AbstractHotelInterestPoint
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * Alias for getTranslate(self::TRANSLATABLE_NAME)
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableString
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME, $locale);
    }
}