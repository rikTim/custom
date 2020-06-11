<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Dictionary\Room;
use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
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
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractHotelRoom
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\MappedSuperclass()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="ROOM_TYPE_ID_IDX", columns={"type_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class AbstractHotelRoom implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface
{
    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    private const TRANSLATABLE_NAME = 'name';
    private const TRANSLATABLE_DESCRIPTION = 'description';

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Room")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * @var Room|null
     * @Groups({"public"})
     */
    private $type;

    /**
     * @return string[]
     */
    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableString::class,
            self::TRANSLATABLE_DESCRIPTION => TranslatableText::class,
        ];
    }

    /**
     * @inheritdoc
     *
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            EqualComparator::attributeFactory('type'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new SetterAttribute('type')
        );
    }

    /**
     * @return Room|null
     */
    public function getType(): ?Room
    {
        return $this->type;
    }

    /**
     * @param Room|null $type
     * @return AbstractHotelRoom
     */
    public function setType(?Room $type): AbstractHotelRoom
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCharacteristicCode(): ?string
    {
        return $this->characteristicCode;
    }

    /**
     * @param null|string $characteristicCode
     * @return AbstractHotelRoom
     */
    public function setCharacteristicCode(?string $characteristicCode): AbstractHotelRoom
    {
        $this->characteristicCode = $characteristicCode;
        return $this;
    }

    /**
     * Alias for getTranslate(self::TRANSLATABLE_NAME)
     *
     * @param Locale|null $locale
     * @return TranslatableString
     * @Groups({"translate"})
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME, $locale);
    }

    /**
     * Alias for getTranslate(self::TRANSLATABLE_DESCRIPTION)
     * @param Locale|null $locale
     * @return TranslatableText
     * @Groups({"public"})
     */
    public function getDescription(Locale $locale = null): TranslatableText
    {
        return $this->getTranslate(self::TRANSLATABLE_DESCRIPTION, $locale);
    }


}