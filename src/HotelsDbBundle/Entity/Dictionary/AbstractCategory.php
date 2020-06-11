<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
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
 * Class AbstractCategory
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\MappedSuperclass()
 * @ORM\Table(indexes={@ORM\Index(name="ACCOMMODATION_IDX", columns={"accommodation_type_id"})})
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractCategory implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface
{
    private const TRANSLATABLE_NAME = 'name';

    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    /**
     * @ORM\Column(name="simple_code", type="integer", length=2, nullable=false)
     * @var integer
     */
    private $simple;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Accommodation")
     * @ORM\JoinColumn(name="accommodation_type_id", referencedColumnName="id", nullable=false)
     * @var Accommodation
     */
    private $accommodation;

    /**
     * @ORM\Column(name="`group`", type="string", length=45, nullable=true)
     * @var string|null
     */
    private $group;

    /**
     * @return array
     */
    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableString::class
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getName() . " ({$this->getSimple()}*)";
    }

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            ScalarHydrator::getIntegerAttribute('simple'),
            new SetterAttribute('accommodation'),
            ScalarHydrator::getStringAttribute('group')
        );
    }

    /**
     * @return GetterMapping
     */
    public function getGetterMapping(): GetterMapping
    {
       return new GetterMapping(
           new GetterAttribute('simple'),
           new GetterAttribute('accommodation'),
           new GetterAttribute('group'),
           TranslatableObjectComparator::attributeFactory()
       );
    }


    /**
     * @return null|string
     */
    public function getSimple(): ?string
    {
        return $this->simple;
    }

    /**
     * @param int|null $simple
     * @return $this
     */
    public function setSimple(?int $simple = null)
    {
        $this->simple = $simple;
        return $this;
    }

    /**
     * @return Accommodation|null
     */
    public function getAccommodation(): ?Accommodation
    {
        return $this->accommodation;
    }

    /**
     * @param Accommodation $accommodation
     * @return $this
     */
    public function setAccommodation(?Accommodation $accommodation): AbstractCategory
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param null|string $group
     * @return $this
     */
    public function setGroup(?string $group = null): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableString
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME,$locale);
    }
}