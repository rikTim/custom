<?php


namespace Base\HotelsDbBundle\Entity\Location;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasLocationAliasTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Behat\Transliterator\Transliterator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractCountry
 * @package Apl\HotelsDbBundle\Entity\Location
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractCountry implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface, \JsonSerializable
{
    private const TRANSLATABLE_NAME = 'name';
    private const TRANSLATABLE_DESCRIPTION = 'description';

    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait,
        HasLocationAliasTrait;

    /**
     * @ORM\Column(name="iso_code", type="string", length=3, nullable=false, options={"fixed":true})
     * @var string
     */
    private $isoCode;

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
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            ScalarHydrator::getStringAttribute('alias'),
            ScalarHydrator::getStringAttribute('isoCode')
        );
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            EqualComparator::attributeFactory('alias'),
            new GetterAttribute('isoCode'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * @return string
     */
    public function getIsoCode(): ?string
    {
        return $this->isoCode;
    }

    /**
     * @param string $isoCode
     * @return $this
     */
    public function setIsoCode(?string $isoCode = null)
    {
        $this->isoCode = $isoCode;
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

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getName();
    }

    /**
     * Alias for getTranslate(self::TRANSLATABLE_DESCRIPTION)
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableText
     */
    public function getDescription(Locale $locale = null): TranslatableText
    {
        return $this->getTranslate(self::TRANSLATABLE_DESCRIPTION, $locale);
    }

    /**
     * @ORM\PreFlush()
     */
    public function generateAliasOnPersist()
    {
        if (!(string)$this->getAlias() && $name = (string)$this->getName(new Locale('en'))) {
            $this->setAlias(Transliterator::transliterate($name));
        }
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'isoCode' => $this->getIsoCode(),
            'name' => (string)$this->getName()
        ];
    }
}