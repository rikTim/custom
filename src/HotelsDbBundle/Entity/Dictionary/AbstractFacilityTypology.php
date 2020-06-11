<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractFacilityTypology
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractFacilityTypology implements HasSetterMappingInterface, HasGetterMappingInterface, ComparableInterface
{
    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait;

    /**
     * @ORM\Column(name="number_flag", type="boolean")
     * @var boolean
     */
    private $number = false;

    /**
     * @ORM\Column(name="logic_flag", type="boolean")
     * @var boolean
     */
    private $logic = false;

    /**
     * @ORM\Column(name="fee_flag", type="boolean")
     * @var boolean
     */
    private $fee = false;

    /**
     * @ORM\Column(name="distance_flag", type="boolean")
     * @var boolean
     */
    private $distance = false;

    /**
     * @ORM\Column(name="age_from_flag", type="boolean")
     * @var boolean
     */
    private $ageFrom = false;

    /**
     * @ORM\Column(name="age_to_flag", type="boolean")
     * @var boolean
     */
    private $ageTo = false;

    /**
     * @ORM\Column(name="date_from_flag", type="boolean")
     * @var boolean
     */
    private $dateFrom = false;

    /**
     * @ORM\Column(name="date_to_flag", type="boolean")
     * @var boolean
     */
    private $dateTo = false;

    /**
     * @ORM\Column(name="time_from_flag", type="boolean")
     * @var boolean
     */
    private $timeFrom = false;

    /**
     * @ORM\Column(name="time_to_flag", type="boolean")
     * @var boolean
     */
    private $timeTo = false;

    /**
     * @ORM\Column(name="ind_yes_or_no_flag", type="boolean")
     * @var boolean
     */
    private $indYesOrNo = false;

    /**
     * @ORM\Column(name="amount_flag", type="boolean")
     * @var boolean
     */
    private $amount = false;

    /**
     * @ORM\Column(name="currency_flag", type="boolean")
     * @var boolean
     */
    private $currency = false;

    /**
     * @ORM\Column(name="app_type_flag", type="boolean")
     * @var boolean
     */
    private $appType = false;

    /**
     * @ORM\Column(name="text_flag", type="boolean")
     * @var boolean
     */
    private $text = false;

    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            ScalarHydrator::getBooleanAttribute('number'),
            ScalarHydrator::getBooleanAttribute('logic'),
            ScalarHydrator::getBooleanAttribute('fee'),
            ScalarHydrator::getBooleanAttribute('distance'),
            ScalarHydrator::getBooleanAttribute('ageFrom'),
            ScalarHydrator::getBooleanAttribute('ageTo'),
            ScalarHydrator::getBooleanAttribute('dateFrom'),
            ScalarHydrator::getBooleanAttribute('dateTo'),
            ScalarHydrator::getBooleanAttribute('timeFrom'),
            ScalarHydrator::getBooleanAttribute('timeTo'),
            ScalarHydrator::getBooleanAttribute('indYesOrNo'),
            ScalarHydrator::getBooleanAttribute('amount'),
            ScalarHydrator::getBooleanAttribute('currency'),
            ScalarHydrator::getBooleanAttribute('appType'),
            ScalarHydrator::getBooleanAttribute('text')
        );
    }

    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('number', 'isNumber'),
            new GetterAttribute('logic', 'isLogic'),
            new GetterAttribute('fee', 'isFee'),
            new GetterAttribute('distance', 'isDistance'),
            new GetterAttribute('ageFrom', 'isAgeFrom'),
            new GetterAttribute('ageTo', 'isAgeTo'),
            new GetterAttribute('dateFrom', 'isDateFrom'),
            new GetterAttribute('dateTo', 'isDateTo'),
            new GetterAttribute('timeFrom', 'isTimeFrom'),
            new GetterAttribute('timeTo', 'isTimeTo'),
            new GetterAttribute('indYesOrNo', 'isIndYesOrNo'),
            new GetterAttribute('amount', 'isAmount'),
            new GetterAttribute('currency', 'isCurrency'),
            new GetterAttribute('appType', 'isAppType'),
            new GetterAttribute('text', 'isText')
        );
    }

    /**
     * @return bool
     */
    public function isNumber(): bool
    {
        return $this->number;
    }

    /**
     * @param bool $number
     * @return AbstractFacilityTypology
     */
    public function setNumber(bool $number): AbstractFacilityTypology
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLogic(): bool
    {
        return $this->logic;
    }

    /**
     * @param bool $logic
     * @return AbstractFacilityTypology
     */
    public function setLogic(bool $logic): AbstractFacilityTypology
    {
        $this->logic = $logic;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFee(): bool
    {
        return $this->fee;
    }

    /**
     * @param bool $fee
     * @return AbstractFacilityTypology
     */
    public function setFee(bool $fee): AbstractFacilityTypology
    {
        $this->fee = $fee;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDistance(): bool
    {
        return $this->distance;
    }

    /**
     * @param bool $distance
     * @return AbstractFacilityTypology
     */
    public function setDistance(bool $distance): AbstractFacilityTypology
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAgeFrom(): bool
    {
        return $this->ageFrom;
    }

    /**
     * @param bool $ageFrom
     * @return AbstractFacilityTypology
     */
    public function setAgeFrom(bool $ageFrom): AbstractFacilityTypology
    {
        $this->ageFrom = $ageFrom;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAgeTo(): bool
    {
        return $this->ageTo;
    }

    /**
     * @param bool $ageTo
     * @return AbstractFacilityTypology
     */
    public function setAgeTo(bool $ageTo): AbstractFacilityTypology
    {
        $this->ageTo = $ageTo;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDateFrom(): bool
    {
        return $this->dateFrom;
    }

    /**
     * @param bool $dateFrom
     * @return AbstractFacilityTypology
     */
    public function setDateFrom(bool $dateFrom): AbstractFacilityTypology
    {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDateTo(): bool
    {
        return $this->dateTo;
    }

    /**
     * @param bool $dateTo
     * @return AbstractFacilityTypology
     */
    public function setDateTo(bool $dateTo): AbstractFacilityTypology
    {
        $this->dateTo = $dateTo;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTimeFrom(): bool
    {
        return $this->timeFrom;
    }

    /**
     * @param bool $timeFrom
     * @return AbstractFacilityTypology
     */
    public function setTimeFrom(bool $timeFrom): AbstractFacilityTypology
    {
        $this->timeFrom = $timeFrom;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTimeTo(): bool
    {
        return $this->timeTo;
    }

    /**
     * @param bool $timeTo
     * @return AbstractFacilityTypology
     */
    public function setTimeTo(bool $timeTo): AbstractFacilityTypology
    {
        $this->timeTo = $timeTo;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIndYesOrNo(): bool
    {
        return $this->indYesOrNo;
    }

    /**
     * @param bool $indYesOrNo
     * @return AbstractFacilityTypology
     */
    public function setIndYesOrNo(bool $indYesOrNo): AbstractFacilityTypology
    {
        $this->indYesOrNo = $indYesOrNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAmount(): bool
    {
        return $this->amount;
    }

    /**
     * @param bool $amount
     * @return AbstractFacilityTypology
     */
    public function setAmount(bool $amount): AbstractFacilityTypology
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCurrency(): bool
    {
        return $this->currency;
    }

    /**
     * @param bool $currency
     * @return AbstractFacilityTypology
     */
    public function setCurrency(bool $currency): AbstractFacilityTypology
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAppType(): bool
    {
        return $this->appType;
    }

    /**
     * @param bool $appType
     * @return AbstractFacilityTypology
     */
    public function setAppType(bool $appType): AbstractFacilityTypology
    {
        $this->appType = $appType;
        return $this;
    }

    /**
     * @return bool
     */
    public function isText(): bool
    {
        return $this->text;
    }

    /**
     * @param bool $text
     * @return AbstractFacilityTypology
     */
    public function setText(bool $text): AbstractFacilityTypology
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedAttributes(): array
    {
        return array_filter($this->getGetterMapping()->valuesToArray($this));
    }

    /**
     * @param ComparableInterface $item
     * @return int
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof AbstractFacilityTypology) || get_class($this) !== get_class($item)) {
            throw new RuntimeException('Cannot compare facility with different class');
        }

        // Даже если айдишники совпадают один их объектов может быть изменен, поэтому требуется фактическая проверка
        if (($diff = max(-1, min(1, $this->getId() - $item->getId()))) !== 0) {
            return $diff;
        }

        return !array_diff($this->getAllowedAttributes(), $item->getAllowedAttributes()) ? 0 : 1;
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