<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasOrderTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractHotelRoomStay
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
class AbstractHotelRoomStay implements TranslatableObjectInterface, HasSetterMappingInterface, HasGetterMappingInterface
{
    private const TRANSLATABLE_NAME = 'name';

    use HasIntegerIdTrait,
        HasOrderTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

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
            ScalarHydrator::getIntegerAttribute('order')
        );
    }

    /**
     * @inheritdoc
     */
    public static function isAllowProbabilisticResolve(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('order'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * Alias for getTranslate(self::TRANSLATABLE_NAME)
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableString
     * @Groups({"translate"})
    
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME, $locale);
    }
}