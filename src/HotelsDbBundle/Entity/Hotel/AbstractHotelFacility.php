<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Dictionary\Currency;
use Apl\HotelsDbBundle\Entity\Dictionary\Facility;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasOrderTrait;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\DateTimeHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractHotelFacility
 * @package Apl\HotelsDbBundle\Entity\Traits
 *
 * @ORM\MappedSuperclass()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="FACILITY_ID_IDX", columns={"facility_id"}),
 *     @ORM\Index(name="CURRENCY_CODE_IDX", columns={"currency_code"}),
 * })
 */
abstract class AbstractHotelFacility implements HasGetterMappingInterface, HasSetterMappingInterface
{
    use HasIntegerIdTrait,
        HasOrderTrait,
        HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Facility")
     * @ORM\JoinColumn(name="facility_id", referencedColumnName="id", nullable=false)
     * @var Facility|null
     */
    private $facility;

    /**
     * @ORM\Column(name="`number`", type="float", nullable=true)
     * @var float|null
     * @Groups({"public"})
     */
    private $number;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     * @Groups({"public"})
     */
    private $logic;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    private $fee;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     * @var int|null
     */
    private $distance;

    /**
     * @ORM\Column(name="age_from", type="smallint", nullable=true, options={"unsigned"=true})
     * @var int|null
     */
    private $ageFrom;

    /**
     * @ORM\Column(name="age_to", type="smallint", nullable=true, options={"unsigned"=true})
     * @var int|null
     */
    private $ageTo;

    /**
     * @ORM\Column(name="date_from", type="datetime_immutable", nullable=true)
     * @var \DateTimeImmutable|null
     */
    private $dateFrom;

    /**
     * @ORM\Column(name="date_to", type="datetime_immutable", nullable=true)
     * @var \DateTimeImmutable|null
     */
    private $dateTo;

    /**
     * @ORM\Column(name="time_from", type="time_immutable", nullable=true)
     * @var \DateTimeImmutable|null
     */
    private $timeFrom;

    /**
     * @ORM\Column(name="time_to", type="time_immutable", nullable=true)
     * @var \DateTimeImmutable|null
     */
    private $timeTo;

    /**
     * @ORM\Column(name="ind_yes_or_no", type="boolean", nullable=true)
     * @var bool|null
     * @Groups({"public"})
     */
    private $indYesOrNo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @var float|null
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Currency")
     * @ORM\JoinColumn(name="currency_code", referencedColumnName="iso_code", nullable=true)
     * @var Currency|null
     */
    private $currency;

    /**
     * @ORM\Column(name="app_type", type="string", nullable=true)
     * @var string|null
     */
    private $appType;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $text;

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            EqualComparator::attributeFactory('facility'),
            new GetterAttribute('number'),
            new GetterAttribute('logic'),
            new GetterAttribute('fee'),
            new GetterAttribute('distance'),
            new GetterAttribute('ageFrom'),
            new GetterAttribute('ageTo'),
            new GetterAttribute('dateFrom'),
            new GetterAttribute('dateTo'),
            new GetterAttribute('timeFrom'),
            new GetterAttribute('timeTo'),
            new GetterAttribute('indYesOrNo'),
            new GetterAttribute('amount'),
            EqualComparator::attributeFactory('currency'),
            new GetterAttribute('appType'),
            new GetterAttribute('text'),
            new GetterAttribute('order')
        );
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new SetterAttribute('facility'),
            ScalarHydrator::getFloatAttribute('number'),
            ScalarHydrator::getBooleanAttribute('logic'),
            ScalarHydrator::getBooleanAttribute('fee'),
            ScalarHydrator::getIntegerAttribute('distance'),
            ScalarHydrator::getIntegerAttribute('ageFrom'),
            ScalarHydrator::getIntegerAttribute('ageTo'),
            DateTimeHydrator::attributeFactory('dateFrom'),
            DateTimeHydrator::attributeFactory('dateTo'),
            DateTimeHydrator::attributeFactory('timeFrom'),
            DateTimeHydrator::attributeFactory('timeTo'),
            ScalarHydrator::getBooleanAttribute('indYesOrNo'),
            ScalarHydrator::getFloatAttribute('amount'),
            new SetterAttribute('currency'),
            ScalarHydrator::getStringAttribute('appType'),
            ScalarHydrator::getStringAttribute('text'),
            ScalarHydrator::getIntegerAttribute('order')
        );
    }

    /**
     * @return Facility|null
     * @Groups({"public"})
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility|null $facility
     * @return AbstractHotelFacility
     */
    public function setFacility(?Facility $facility): AbstractHotelFacility
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getNumber(): ?float
    {
        return $this->number;
    }

    /**
     * @param float|null $number
     * @return AbstractHotelFacility
     */
    public function setNumber(?float $number): AbstractHotelFacility
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getLogic(): ?bool
    {
        return $this->logic;
    }

    /**
     * @param bool|null $logic
     * @return AbstractHotelFacility
     */
    public function setLogic(?bool $logic): AbstractHotelFacility
    {
        $this->logic = $logic;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFee(): ?bool
    {
        return $this->fee;
    }

    /**
     * @param bool|null $fee
     * @return AbstractHotelFacility
     */
    public function setFee(?bool $fee): AbstractHotelFacility
    {
        $this->fee = $fee;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDistance(): ?int
    {
        return $this->distance;
    }

    /**
     * @param int|null $distance
     * @return AbstractHotelFacility
     */
    public function setDistance(?int $distance): AbstractHotelFacility
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAgeFrom(): ?int
    {
        return $this->ageFrom;
    }

    /**
     * @param int|null $ageFrom
     * @return AbstractHotelFacility
     */
    public function setAgeFrom(?int $ageFrom): AbstractHotelFacility
    {
        $this->ageFrom = $ageFrom;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAgeTo(): ?int
    {
        return $this->ageTo;
    }

    /**
     * @param int|null $ageTo
     * @return AbstractHotelFacility
     */
    public function setAgeTo(?int $ageTo): AbstractHotelFacility
    {
        $this->ageTo = $ageTo;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateFrom(): ?\DateTimeImmutable
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTimeImmutable|null $dateFrom
     * @return AbstractHotelFacility
     */
    public function setDateFrom(?\DateTimeImmutable $dateFrom): AbstractHotelFacility
    {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateTo(): ?\DateTimeImmutable
    {
        return $this->dateTo;
    }

    /**
     * @param \DateTimeImmutable|null $dateTo
     * @return AbstractHotelFacility
     */
    public function setDateTo(?\DateTimeImmutable $dateTo): AbstractHotelFacility
    {
        $this->dateTo = $dateTo;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimeFrom(): ?\DateTimeImmutable
    {
        return $this->timeFrom;
    }

    /**
     * @param \DateTimeImmutable|null $timeFrom
     * @return AbstractHotelFacility
     */
    public function setTimeFrom(?\DateTimeImmutable $timeFrom): AbstractHotelFacility
    {
        $this->timeFrom = $timeFrom;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimeTo(): ?\DateTimeImmutable
    {
        return $this->timeTo;
    }

    /**
     * @param \DateTimeImmutable|null $timeTo
     * @return AbstractHotelFacility
     */
    public function setTimeTo(?\DateTimeImmutable $timeTo): AbstractHotelFacility
    {
        $this->timeTo = $timeTo;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIndYesOrNo(): ?bool
    {
        return $this->indYesOrNo;
    }

    /**
     * @param bool|null $indYesOrNo
     * @return AbstractHotelFacility
     */
    public function setIndYesOrNo(?bool $indYesOrNo): AbstractHotelFacility
    {
        $this->indYesOrNo = $indYesOrNo;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float|null $amount
     * @return AbstractHotelFacility
     */
    public function setAmount(?float $amount): AbstractHotelFacility
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return Currency|null
     */
    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    /**
     * @param Currency|null $currency
     * @return AbstractHotelFacility
     */
    public function setCurrency(?Currency $currency): AbstractHotelFacility
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAppType(): ?string
    {
        return $this->appType;
    }

    /**
     * @param null|string $appType
     * @return AbstractHotelFacility
     */
    public function setAppType(?string $appType): AbstractHotelFacility
    {
        $this->appType = $appType;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param null|string $text
     * @return AbstractHotelFacility
     */
    public function setText(?string $text): AbstractHotelFacility
    {
        $this->text = $text;
        return $this;
    }
}