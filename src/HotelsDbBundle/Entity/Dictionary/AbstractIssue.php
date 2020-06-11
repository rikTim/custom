<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
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

/**
 * Class AbstractIssue
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractIssue implements TranslatableObjectInterface,HasGetterMappingInterface, HasSetterMappingInterface
{
    private const TRANSLATABLE_NAME = 'name';
    private const TRANSLATABLE_DESCRIPTION = 'description';

    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;


    /**
     * @ORM\Column(name="type", type="string", length=125, nullable=false)
     * @var string
     */
    public $type;

    /**
     * @ORM\Column(name="alternative", type="boolean")
     * @var
     */
    public $alternative;

    /**
     * @return string[]
     */
    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableText::class,
            self::TRANSLATABLE_DESCRIPTION => TranslatableText::class,
        ];
    }

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            ScalarHydrator::getStringAttribute('type'),
            ScalarHydrator::getIntegerAttribute('alternative')
        );
    }


    /**
     * @return GetterMapping
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('type'),
            new GetterAttribute('alternative'),
            TranslatableObjectComparator::attributeFactory()
        );
    }


    /**
     * @return string
     */
    public function getType():?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(?string $type = null )
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAlternative():?bool
    {
        return $this->alternative;
    }

    /**
     * @param bool $alternative
     * @return $this
     */
    public function setAlternative(?bool $alternative = false)
    {
        $this->alternative = $alternative;
        return $this;
    }

    /**
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableText
     */
    public function getName(Locale $locale = null): TranslatableText
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