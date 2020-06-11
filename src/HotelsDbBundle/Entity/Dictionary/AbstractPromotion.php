<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractPromotions
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractPromotion implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface
{
    private const TRANSLATABLE_NAME = 'name';
    private const TRANSLATABLE_DESCRIPTION = 'description';

    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

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
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping();
    }

    /**
     * @return GetterMapping
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableString
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME, $locale);
    }

    /**
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableText
     */
    public function getDescription(Locale $locale = null): TranslatableText
    {
        return $this->getTranslate(self::TRANSLATABLE_DESCRIPTION, $locale);
    }
}