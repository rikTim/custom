<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasLocationAliasTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
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
use Apl\HotelstonApiBundle\Type\Locale;
use Behat\Transliterator\Transliterator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractFacility
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractFacility implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface, ComparableInterface
{
    private const TRANSLATABLE_NAME = 'name';

    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\FacilityGroup")
     * @ORM\JoinColumn(name="facility_group_code", referencedColumnName="id", nullable=true)
     * @var FacilityGroup
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\FacilityTypology")
     * @ORM\JoinColumn(name="facility_typology_code", referencedColumnName="id", nullable=false)
     * @var FacilityTypology
     */
    private $typology;



    public static function getTranslateMapping(): array
    {
        return [
            'name' => TranslatableString::class,
        ];
    }

    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new SetterAttribute('group'),
            new SetterAttribute('typology')
        );
    }

    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('group'),
            new GetterAttribute('typology'),
            TranslatableObjectComparator::attributeFactory()
        );
    }


    /**
     * @return FacilityTypology|null
     */
    public function getTypology(): ?FacilityTypology
    {
        return $this->typology;
    }

    /**
     * @param FacilityTypology|null $typology
     * @return AbstractFacility
     */
    public function setTypology(?FacilityTypology $typology): AbstractFacility
    {
        $this->typology = $typology;
        return $this;
    }

    /**
     * @return FacilityGroup|null
     */
    public function getGroup(): ?FacilityGroup
    {
        return $this->group;
    }

    /**
     * @param FacilityGroup|null $group
     * @return AbstractFacility
     */
    public function setGroup(?FacilityGroup $group): AbstractFacility
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableString
     * @Groups({"translate"})
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME, $locale);
    }

    /**
     * @param ComparableInterface $item
     * @return int
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof AbstractFacility) || get_class($this) !== get_class($item)) {
            throw new RuntimeException('Cannot compare facility with different class');
        }

        // Даже если айдишники совпадают один их объектов может быть изменен, поэтому требуется фактическая проверка
        if (($diff = max(-1, min(1, $this->getId() - $item->getId()))) !== 0) {
            return $diff;
        }

        if (!$item->getTypology()) {
            return 1;
        }

        if (!$this->getTypology()) {
            return -1;
        }

        return $this->getTypology()->compare($this->getTypology());
    }

    /**
     * @param ComparableInterface $item
     * @return bool
     */
    public function isEqual(ComparableInterface $item): bool
    {
        return $this->compare($item) === 0;
    }
}