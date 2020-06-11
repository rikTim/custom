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
 * Class AbstractRoom
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractRoom implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface, \JsonSerializable
{
    private const TRANSLATABLE_NAME = 'name';
    private const TRANSLATABLE_DESCRIPTION = 'description';

    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    /**
     * @ORM\Column(name="type", type="string", length=25, nullable=false)
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(name="characteristic", type="string", length=25)
     * @var string
     */
    private $characteristic;

    /**
     * @ORM\Column(name="min_pax", type="integer", length=4)
     * @var integer
     */
    private $minPax;

    /**
     * @ORM\Column(name="max_pax", type="integer", length=4)
     * @var integer
     */
    private $maxPax;

    /**
     * @ORM\Column(name="max_adults", type="integer", length=4)
     * @var integer
     */
    private $maxAdults;

    /**
     * @ORM\Column(name="max_children", type="integer", length=4)
     * @var integer
     */
    private $maxChildren;

    /**
     * @ORM\Column(name="min_adults", type="integer", length=4)
     * @var integer
     */
    private $minAdults;

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
            ScalarHydrator::getStringAttribute('type'),
            ScalarHydrator::getStringAttribute('characteristic'),
            ScalarHydrator::getIntegerAttribute('minPax'),
            ScalarHydrator::getIntegerAttribute('maxPax'),
            ScalarHydrator::getIntegerAttribute('maxAdults'),
            ScalarHydrator::getIntegerAttribute('maxChildren'),
            ScalarHydrator::getIntegerAttribute('minAdults')
        );
    }


    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('type'),
            new GetterAttribute('characteristic'),
            new GetterAttribute('minPax'),
            new GetterAttribute('maxPax'),
            new GetterAttribute('maxAdults'),
            new GetterAttribute('minAdults'),
            new GetterAttribute('maxChildren'),
            TranslatableObjectComparator::attributeFactory()
        );
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
    public function setType(?string $type = null): AbstractRoom
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCharacteristic(): ?string
    {
        return $this->characteristic;
    }

    /**
     * @param null|string $characteristic
     * @return $this
     */
    public function setCharacteristic(?string $characteristic = null): AbstractRoom
    {
        $this->characteristic = $characteristic;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxPax(): ?int
    {
        return $this->maxPax;
    }

    /**
     * @param int|null $maxPax
     * @return $this
     */
    public function setMaxPax(?int $maxPax): AbstractRoom
    {
        $this->maxPax = $maxPax;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinPax(): ?int
    {
        return $this->minPax;
    }

    /**
     * @param int|null $minPax
     * @return $this
     */
    public function setMinPax(?int $minPax): AbstractRoom
    {
        $this->minPax = $minPax;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxAdults(): ?int
    {
        return $this->maxAdults;
    }

    /**
     * @param int|null $maxAdults
     * @return $this
     */
    public function setMaxAdults(?int $maxAdults): AbstractRoom
    {
        $this->maxAdults = $maxAdults;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinAdults(): ?int
    {
        return $this->minAdults;
    }

    /**
     * @param int|null $minAdults
     * @return $this
     */
    public function setMinAdults(?int $minAdults): AbstractRoom
    {
        $this->minAdults = $minAdults;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxChildren(): ?int
    {
        return $this->maxChildren;
    }

    /**
     * @param int|null $maxChildren
     * @return $this
     */
    public function setMaxChildren(?int $maxChildren): AbstractRoom
    {
        $this->maxChildren = $maxChildren;
        return $this;
    }


    /**
     * @param Locale|null $locale
     * @return TranslateTypeInterface
     * @Groups({"translate"})
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME, $locale);
    }

    /**
     * @param Locale|null $locale
     * @return TranslateTypeInterface
     * @Groups({"translate"})
     */
    public function getDescription(Locale $locale = null): TranslatableText
    {
        return $this->getTranslate(self::TRANSLATABLE_DESCRIPTION, $locale);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'characteristic' => $this->getCharacteristic(),
            'minPax' => $this->getMinPax(),
            'maxPax' => $this->getMaxPax(),
            'maxAdults' => $this->getMaxAdults(),
            'maxChildren' => $this->getMaxChildren(),
            'minAdults' => $this->getMinAdults(),
        ];
    }
}