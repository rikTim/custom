<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Currency
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\CurrencyRepository")
 * @ORM\Table(name="hotels_db_dictionary_currency")
 * @ORM\HasLifecycleCallbacks()
 */
class Currency implements ServiceProviderReferencedEntityInterface, HasSetterMappingInterface, HasGetterMappingInterface, TranslatableObjectInterface
{
    private const TRANSLATABLE_NAME = 'name';

    use HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait,
        HasTranslationTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(name="iso_code", type="string", length=3, nullable=false, options={"fixed"=true})
     * @var string
     */
    private  $isoCode;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\CurrencySPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var CurrencySPReference[]|ArrayCollection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\Column(name="currency_type", type="string", length=125, nullable=false)
     * @var string
     */
    private  $type;

    /**
     * @return string[]
     */
    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableString::class,
        ];
    }

    /**
     * Currency constructor.
     */
    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getTranslateId(): ?int
    {
        return crc32($this->getIsoCode());
    }

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            ScalarHydrator::getStringAttribute('isoCode'),
            ScalarHydrator::getStringAttribute('type'),
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_MERGE)
        );
    }

    /**
     * @return GetterMapping
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('type'),
            new GetterAttribute('isoCode'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * @return CurrencySPReference[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
       return $this->serviceProviderReferences;
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
    public function setIsoCode(?string $isoCode = null): Currency
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
    public function setType(?string $type = null): Currency
    {
        $this->type = $type;
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
}