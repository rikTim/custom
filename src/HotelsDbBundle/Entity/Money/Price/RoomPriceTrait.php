<?php


namespace Apl\HotelsDbBundle\Entity\Money\Price;


use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Hotel\HotelRoom;
use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasEmbeddedServiceProviderReference;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\CancellationPolicy;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\CancellationPolicyInterface;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\RoomPriceInterface;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\Tax;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\TaxInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\DateTimeComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\DateTimeHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class RoomPriceTrait
 *
 * @package Apl\HotelsDbBundle\Entity\Money\Money
 */
trait RoomPriceTrait
{
    use HasEmbeddedServiceProviderReference,
        HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel")
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)
     * @var Hotel
     */
    private $hotel;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoom")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=true)
     * @var HotelRoom|null
     */
    private $room;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Board")
     * @ORM\JoinColumn(name="board_id", referencedColumnName="id", nullable=true)
     * @var Board|null
     */
    private $board;

    /**
     * @ORM\Column(name="check_in", type="date_immutable", nullable=false)
     * @var \DateTimeImmutable
     */
    private $checkIn;

    /**
     * @ORM\Column(name="check_out", type="date_immutable", nullable=false)
     * @var \DateTimeImmutable
     */
    private $checkOut;

    /**
     * @ORM\Column(name="room_quantity", type="smallint", nullable=false, options={"unsigned"=true})
     * @var integer
     */
    private $roomQuantity;

    /**
     * @ORM\Column(name="adults", type="smallint", nullable=false, options={"unsigned"=true})
     * @var integer
     */
    private $adults;

    /**
     * @ORM\Column(name="children", type="smallint", nullable=false, options={"unsigned"=true})
     * @var integer
     */
    private $children;

    /**
     * @ORM\Column(name="children_ages", type="simple_array", nullable=true)
     * @var integer[]
     */
    private $childrenAges;

    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Money\Money", columnPrefix="rate_net_")
     * @Groups({"secured"})
     * @var Money
     */
    private $rateNet;

    /**
     * @ORM\Column(name="rate_class", type="string", length=3, nullable=true, options={"fixed": true})
     * @var string|null
     */
    private $rateClass;

    /**
     * @ORM\Column(name="rate_type", type="string", length=16, nullable=true)
     * @var string|null
     */
    private $rateType;

    /**
     * @ORM\Column(name="payment_type", length=16, nullable=true)
     * @var string|null
     */
    private $paymentType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    private $packaging;

    /**
     * @ORM\Column(name="booking_remarks", type="json", nullable=true)
     * @var string[]
     */
    private $bookingRemarks;

    /**
     * @ORM\Column(name="cancellation_policies", type="json", nullable=true)
     * @var array[]
     */
    private $rawCancellationPolicies = [];

    /**
     * @var CancellationPolicyInterface[]
     */
    private $cancellationPolicies;

    /**
     * @ORM\Column(name="taxes", type="json", nullable=true)
     * @var array[]
     */
    private $rawTaxes = [];

    /**
     * @var TaxInterface[]
     */
    private $taxes;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var array[]
     */
    private $offers;

    /**
     * {@inheritdoc}
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            EqualComparator::attributeFactory('serviceProviderReference'),
            EqualComparator::attributeFactory('hotel'),
            EqualComparator::attributeFactory('room'),
            EqualComparator::attributeFactory('board'),
            DateTimeComparator::attributeFactory('checkIn'),
            DateTimeComparator::attributeFactory('checkOut'),
            new GetterAttribute('roomQuantity'),
            new GetterAttribute('adults'),
            new GetterAttribute('children'),
            new GetterAttribute('childrenAges'),
            EqualComparator::attributeFactory('rateNet'),
            new GetterAttribute('rateClass'),
            new GetterAttribute('rateType'),
            new GetterAttribute('paymentType'),
            new GetterAttribute('packaging'),
            new GetterAttribute('bookingRemarks'),
            new GetterAttribute('cancellationPolicies'),
            new GetterAttribute('taxes'),
            new GetterAttribute('offers')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new SetterAttribute('serviceProviderReference'),
            new SetterAttribute('hotel'),
            new SetterAttribute('room'),
            new SetterAttribute('board'),
            DateTimeHydrator::attributeFactory('checkIn'),
            DateTimeHydrator::attributeFactory('checkOut'),
            ScalarHydrator::getIntegerAttribute('roomQuantity'),
            ScalarHydrator::getIntegerAttribute('adults'),
            ScalarHydrator::getIntegerAttribute('children'),
            new SetterAttribute('childrenAges'),
            new SetterAttribute('rateNet'),
            ScalarHydrator::getStringAttribute('rateClass'),
            ScalarHydrator::getStringAttribute('rateType'),
            ScalarHydrator::getStringAttribute('paymentType'),
            ScalarHydrator::getBooleanAttribute('packaging'),
            new SetterAttribute('bookingRemarks'),
            new SetterAttribute('cancellationPolicies'),
            new SetterAttribute('taxes'),
            new SetterAttribute('offers')
        );
    }

    /**
     * @return Hotel
     */
    public function getHotel(): Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return RoomPriceTrait
     */
    public function setHotel(?Hotel $hotel): self
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return HotelRoom|null
     */
    public function getRoom(): ?HotelRoom
    {
        return $this->room;
    }

    /**
     * @param HotelRoom|null $room
     * @return RoomPriceTrait
     */
    public function setRoom(?HotelRoom $room): self
    {
        $this->room = $room;
        return $this;
    }

    /**
     * @return Board
     */
    public function getBoard(): ?Board
    {
        return $this->board;
    }

    /**
     * @param Board|null $board
     * @return RoomPriceTrait
     */
    public function setBoard(?Board $board): self
    {
        $this->board = $board;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCheckIn(): \DateTimeImmutable
    {
        return $this->checkIn;
    }

    /**
     * @param \DateTimeImmutable $checkIn
     * @return RoomPriceTrait
     */
    public function setCheckIn(\DateTimeImmutable $checkIn): self
    {
        $this->checkIn = $checkIn;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCheckOut(): \DateTimeImmutable
    {
        return $this->checkOut;
    }

    /**
     * @param \DateTimeImmutable $checkOut
     * @return RoomPriceTrait
     */
    public function setCheckOut(\DateTimeImmutable $checkOut): self
    {
        $this->checkOut = $checkOut;
        return $this;
    }

    /**
     * @return int
     */
    public function getRoomQuantity(): int
    {
        return $this->roomQuantity;
    }

    /**
     * @param int $roomQuantity
     * @return RoomPriceTrait
     */
    public function setRoomQuantity(int $roomQuantity): self
    {
        $this->roomQuantity = $roomQuantity;
        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return RoomPriceTrait
     */
    public function setAdults(int $adults): self
    {
        $this->adults = $adults;
        return $this;
    }

    /**
     * @return int
     */
    public function getChildren(): int
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return RoomPriceTrait
     */
    public function setChildren(int $children): self
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return integer[] отсортированный по возврастанию массив с возрастами детей на момент заселения
     */
    public function getChildrenAges(): array
    {
        return $this->childrenAges;
    }

    /**
     * @param integer[] $childrenAges
     * @return RoomPriceTrait
     */
    public function setChildrenAges(array $childrenAges): self
    {
        $this->childrenAges = $childrenAges;
        sort($this->childrenAges);
        return $this;
    }

    /**
     * @return MoneyInterface
     */
    public function getRateNet(): MoneyInterface
    {
        return $this->rateNet;
    }

    /**
     * @param MoneyInterface $rateNet
     * @return RoomPriceTrait
     */
    public function setRateNet(MoneyInterface $rateNet): self
    {
        $this->rateNet = $rateNet;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRateClass(): ?string
    {
        return $this->rateClass;
    }

    /**
     * @param string|null $rateClass
     * @return RoomPriceTrait
     */
    public function setRateClass(?string $rateClass): self
    {
        $this->rateClass = $rateClass;
        return $this;
    }

    /**
     * @return string|string
     */
    public function getRateType(): ?string
    {
        return $this->rateType;
    }

    /**
     * @param string|null $rateType
     * @return RoomPriceTrait
     */
    public function setRateType(?string $rateType): self
    {
        $this->rateType = $rateType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    /**
     * @param string|null $paymentType
     * @return RoomPriceTrait
     */
    public function setPaymentType(?string $paymentType): self
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isPackaging(): ?bool
    {
        return $this->packaging;
    }

    /**
     * @param bool|null $packaging
     * @return RoomPriceTrait
     */
    public function setPackaging(?bool $packaging): self
    {
        $this->packaging = $packaging;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getBookingRemarks(): array
    {
        return $this->bookingRemarks ?? [];
    }

    /**
     * @param string[]|null $bookingRemarks
     * @return RoomPriceInterface|self
     */
    public function setBookingRemarks(?array $bookingRemarks): RoomPriceInterface
    {
        $this->bookingRemarks = \is_array($bookingRemarks) ? array_filter($bookingRemarks) : [];
        return $this;
    }

    /**
     * @return array[]
     */
    public function getCancellationPolicies(): array
    {
        if ($this->cancellationPolicies === null) {
            $this->cancellationPolicies = [];
            foreach ($this->rawCancellationPolicies as $rawCancellationPolicy) {
                $this->cancellationPolicies[] = CancellationPolicy::createFromArray($rawCancellationPolicy);
            }
            usort($this->cancellationPolicies, function ($item1, $item2) {
                return $item1->getFrom()->getTimestamp() - $item2->getFrom()->getTimestamp() ;
            });

        }

        return $this->cancellationPolicies;
    }

    /**
     * @param CancellationPolicyInterface $cancellationPolicy
     * @return RoomPriceInterface|self
     */
    public function addCancellationPolicy(CancellationPolicyInterface $cancellationPolicy): RoomPriceInterface
    {
        $this->rawCancellationPolicies[] = $cancellationPolicy->toArray();

        if ($this->cancellationPolicies !== null) {
            $this->cancellationPolicies[] = $cancellationPolicy;
        }

        return $this;
    }

    /**
     * @return array[]
     */
    public function getRawCancellationPolicies(): array
    {
        return $this->rawCancellationPolicies;
    }

    /**
     * @param array[] $rawCancellationPolicies
     * @return RoomPriceTrait
     */
    public function setRawCancellationPolicies(array $rawCancellationPolicies): self
    {
        $this->rawCancellationPolicies = $rawCancellationPolicies;
        return $this;
    }

    /**
     * @return array[]
     */
    public function getRawTaxes(): array
    {
        return $this->rawTaxes;
    }

    /**
     * @param array[] $rawTaxes
     * @return RoomPriceTrait
     */
    public function setRawTaxes(array $rawTaxes): self
    {
        $this->rawTaxes = $rawTaxes;
        return $this;
    }

    /**
     * @param CancellationPolicyInterface[] $cancellationPolicies
     * @return RoomPriceTrait
     */
    public function setCancellationPolicies(array $cancellationPolicies): self
    {
        $this->rawCancellationPolicies = [];
        $this->cancellationPolicies = [];

        foreach ($cancellationPolicies as $cancellationPolicy) {
            $this->addCancellationPolicy($cancellationPolicy);
        }

        return $this;
    }

    /**
     * @return array[]
     */
    public function getTaxes(): array
    {
        if ($this->taxes === null) {
            $this->taxes = [];
            foreach ($this->rawTaxes as $rawTax) {
                $this->taxes[] = Tax::createFromArray($rawTax);
            }
        }

        return $this->taxes;
    }

    /**
     * @param TaxInterface $tax
     * @return RoomPriceInterface|self
     */
    public function addTax(TaxInterface $tax): RoomPriceInterface
    {
        $this->rawTaxes[] = $tax->toArray();
        if ($this->taxes !== null) {
            $this->taxes[] = $tax;
        }

        return $this;
    }

    /**
     * @param TaxInterface[] $taxes
     * @return RoomPriceTrait
     */
    public function setTaxes(array $taxes): self
    {
        $this->rawTaxes = [];
        $this->taxes = [];

        foreach ($taxes as $tax) {
            $this->addTax($tax);
        }

        return $this;
    }

    /**
     * @return array[]
     */
    public function getOffers(): array
    {
        return $this->offers ?? [];
    }

    /**
     * @param array[] $offers
     * @return RoomPriceTrait
     */
    public function setOffers(array $offers): self
    {
        $this->offers = $offers;
        return $this;
    }

    /**
     * @param ComparableInterface $item
     * @return int
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof RoomPriceInterface) || \get_class($this) !== \get_class($item)) {
            throw new InvalidArgumentException(sprintf('Cannot compare "%s" with different class', \get_class($this)));
        }

        // Нельзя сравнивать разные валюты, но мы должны сказать что-то в ответе без ошибки
        if ($this->getRateNet()->getCurrency() !== $item->getRateNet()->getCurrency()) {
            return strcasecmp($this->getRateNet()->getCurrency(), $item->getRateNet()->getCurrency());
        }

        // Разница в цене
        if (($compareRate = $this->getRateNet()->compare($item->getRateNet())) !== 0) {
            return $compareRate;
        }

        // Разная цена
        $referenceCmp = strcasecmp(
            (string)$this->getServiceProviderReference(),
            (string)$item->getServiceProviderReference()
        );

        if ($referenceCmp !== 0) {
            return $referenceCmp;
        }

        // Прочие данные
        if ($this->getHotel()->getId() !== $item->getHotel()->getId()) {
            $hotelCmp = strcasecmp($this->getHotel()->getName(), $item->getHotel()->getName());
            return $hotelCmp ?: ($this->getHotel()->getId() - $item->getHotel()->getId());
        }

        if ($this->getRoom()->getId() !== $item->getRoom()->getId()) {
            return $this->getRoom()->getId() - $item->getRoom()->getId();
        }

        if (
            $this->getRoomQuantity() !== $item->getRoomQuantity()
            || $this->getAdults() !== $item->getAdults()
            || $this->getRateType() !== $item->getRateType()
            || $this->getPaymentType() !== $item->getPaymentType()
            || $this->isPackaging() !== $item->isPackaging()
            || \array_diff($this->getChildrenAges(), $item->getChildrenAges())
            || \count($this->getChildrenAges()) !== \count($item->getChildrenAges())
            || $this->getCheckIn()->format('Ymd') !== $item->getCheckIn()->format('Ymd')
            || $this->getCheckOut()->format('Ymd') !== $item->getCheckOut()->format('Ymd')
            || $this->getBoard()->getId() !== $item->getBoard()->getId()
        ) {
            return -1;
        }

        // Cancellation policies, tax and offers compare is to hard
        return 0;
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