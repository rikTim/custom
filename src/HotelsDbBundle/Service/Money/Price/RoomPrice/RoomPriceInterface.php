<?php

namespace Apl\HotelsDbBundle\Service\Money\Price\RoomPrice;


use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\EmbeddableServiceProviderReference;
use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Hotel\HotelRoom;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * Class RoomPriceTrait
 *
 * @package Apl\HotelsDbBundle\Entity\Money\Money
 */
interface RoomPriceInterface extends HasGetterMappingInterface, HasSetterMappingInterface
{
    public const PAYMENT_TYPE_AT_WEB = 'AT_WEB';
    public const PAYMENT_TYPE_AT_HOTEL = 'AT_HOTEL';
    public const PAYMENT_TYPE_BOTH = 'BOTH';

    public const RATE_CLASS_NORMAN = 'NOR';
    public const RATE_CLASS_NON_REFUNDABLE = 'NRF';
    public const RATE_CLASS_SPECIAL = 'SPE';
    public const RATE_CLASS_OFFER = 'OFE';
    public const RATE_CLASS_PACKAGE = 'PAQ';
    public const RATE_CLASS_NON_REFUNDABLE_PACKAGE = 'NRP';

    public const RATE_TYPE_BOOKABLE = 'BOOKABLE';
    public const RATE_TYPE_RECHECK = 'RECHECK';

    /**
     * @return EmbeddableServiceProviderReference
     */
    public function getServiceProviderReference(): ?EmbeddableServiceProviderReference;

    /**
     * @return Hotel
     */
    public function getHotel(): Hotel;

    /**
     * @return HotelRoom
     */
    public function getRoom(): ?HotelRoom;

    /**
     * @return Board
     */
    public function getBoard(): ?Board;

    /**
     * @return \DateTimeImmutable
     */
    public function getCheckIn(): \DateTimeImmutable;

    /**
     * @return \DateTimeImmutable
     */
    public function getCheckOut(): \DateTimeImmutable;

    /**
     * @return int
     */
    public function getRoomQuantity(): int;

    /**
     * @return int
     * @Groups("public")
     */
    public function getAdults(): int;

    /**
     * @return int
     * @Groups("public")
     */
    public function getChildren(): int;

    /**
     * @return integer[] отсортированный по возврастанию массив с возрастами детей на момент заселения
     */
    public function getChildrenAges(): array;

    /**
     * @return MoneyInterface
     */
    public function getRateNet(): MoneyInterface;

    /**
     * @return string|null
     */
    public function getRateClass(): ?string;

    /**
     * @return string|null
     */
    public function getRateType(): ?string;

    /**
     * @return string|null
     */
    public function getPaymentType(): ?string;

    /**
     * @return bool|null
     */
    public function isPackaging(): ?bool;

    /**
     * @return array
     */
    public function getBookingRemarks(): array;

    /**
     * @return CancellationPolicyInterface[]
     * @Groups("public")
     */
    public function getCancellationPolicies(): array;

    /**
     * @param CancellationPolicyInterface $cancellationPolicy
     * @return RoomPriceInterface
     */
    public function addCancellationPolicy(CancellationPolicyInterface $cancellationPolicy): RoomPriceInterface;

    /**
     * @return TaxInterface[]
     * @Groups("public")
     */
    public function getTaxes(): array;

    /**
     * @param TaxInterface $tax
     * @return RoomPriceInterface
     */
    public function addTax(TaxInterface $tax): RoomPriceInterface;

    /**
     * @return array[]
     */
    public function getOffers(): array;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreated(): \DateTimeImmutable;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdated(): ?\DateTimeImmutable;
}