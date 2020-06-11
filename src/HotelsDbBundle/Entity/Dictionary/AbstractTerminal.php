<?php

namespace Base\HotelsDbBundle\Entity\Dictionary;


use Base\HotelsDbBundle\Entity\Locale;
use Base\HotelsDbBundle\Entity\Location\Country;
use Base\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Base\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Base\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Base\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Base\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Behat\Transliterator\Transliterator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractTerminal
 * @package Base\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\MappedSuperclass()
 *  @ORM\Table(indexes={
 *     @ORM\Index(name="COUNTRY_IDX", columns={"country_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class AbstractTerminal implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface
{
    private const TRANSLATABLE_NAME = 'name';
    private const TRANSLATABLE_DESCRIPTION = 'description';

    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    /**
     * @ORM\Column(name="iso_code", type="string", length=125, nullable=false)
     * @var string
     */
    private  $isoCode;


    /**
     * @ORM\Column(name="type", type="string", length=3, nullable=false)
     * @var string
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Base\HotelsDbBundle\Entity\Location\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     * @var Country
     */
    private $country;

    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableString::class,
            self::TRANSLATABLE_DESCRIPTION => TranslatableText::class,
        ];
    }

    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            ScalarHydrator::getStringAttribute('isoCode'),
             ScalarHydrator::getStringAttribute('type'),
            new SetterAttribute('country')

        );
    }

    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('isoCode'),
            new GetterAttribute('type'),
            new GetterAttribute('country'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * @return null|string
     */
    public function getIsoCode(): ?string
    {
        return $this->isoCode;
    }

    /**
     * @param null|string $isoCode
     * @return $this
     */
    public function setIsoCode(?string $isoCode = null)
    {
        $this->isoCode = $isoCode;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param null|string $type
     * @return $this
     */
    public function setType(?string $type = null)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country|null $country
     * @return $this
     */
    public function setCountry(?Country $country): AbstractTerminal
    {
        $this->country = $country;
        return $this;
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