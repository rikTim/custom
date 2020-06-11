<?php


namespace Apl\HotelsDbBundle\Service\Money;


use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Money;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * Interface MoneyInterface
 *
 * @package Apl\HotelsDbBundle\Entity\Money\Money
 */
interface MoneyInterface extends ComparableInterface
{
    public const EMPTY_CURRENCY = 'NOP';

    /**
     * @return string
     * @Groups({"money", "secured"})
     */
    public function getAmount(): string;

    /**
     * @param string $amount
     * @return MoneyInterface
     */
    public function setAmount(string $amount): MoneyInterface;

    /**
     * @return string
     * @Groups({"money", "secured"})
     * @SWG\Property(description="Currency code (3 char)")
     */
    public function getCurrency(): string;

    /**
     * @param string $currency
     * @return MoneyInterface
     */
    public function setCurrency(string $currency): MoneyInterface;

    /**
     * @return Money\Money
     */
    public function getMoney(): Money\Money;

    /**
     * @param Money\Money $money
     * @return MoneyInterface
     */
    public function setMoney(Money\Money $money): MoneyInterface;

    /**
     * @return bool
     */
    public function isAvailable(): bool ;

    /**
     * @param array $data
     * @return MoneyInterface
     */
    public static function createFromArray(array $data): MoneyInterface;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @param int $precision
     * @return string
     */
    public function getRoundAmount(int $precision = 0): string;

    /**
     * Warning! Method change current money amount
     *
     * @param MoneyInterface $money
     * @param int $scale
     * @return MoneyInterface
     */
    public function add(MoneyInterface $money, int $scale = 2): MoneyInterface;

    /**
     * Warning! Method change current money amount
     *
     * @param MoneyInterface $money
     * @param int $scale
     * @return MoneyInterface
     */
    public function sub(MoneyInterface $money, int $scale = 2): MoneyInterface;

    /**
     * Warning! Method change current money amount
     *
     * @param string $multiplier
     * @return MoneyInterface
     */
    public function mul(string $multiplier): MoneyInterface;
}