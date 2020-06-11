<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\RoomPrice;


use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Interface TaxInterface
 *
 * @package Apl\HotelsDbBundle\Service\Money\Price\RoomPrice
 */
interface TaxInterface
{
    public const TYPE_PROVIDER_INCLUDED = 'provider_included';

    public const TYPE_PAY_IN_HOTEL = 'pay_in_hotel';

    public const TYPE_OUR_SERVICE_CHARGE = 'our_service_charge';

    public const AVAILABLE_TYPES = [
        self::TYPE_PROVIDER_INCLUDED,
        self::TYPE_PAY_IN_HOTEL,
        self::TYPE_OUR_SERVICE_CHARGE,
    ];

    /**
     * @param array $data
     * @return TaxInterface
     */
    public static function createFromArray(array $data): TaxInterface;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return bool
     * @Groups("public")
     */
    public function isIncluded(): bool;

    /**
     * @return MoneyInterface
     * @Groups("money")
     */
    public function getRate(): MoneyInterface;

    /**
     * @return string
     * @Groups("public")
     */
    public function getType(): string;
}