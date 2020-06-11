<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\RoomPrice;


use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;

/**
 * Class Tax
 *
 * @package Apl\HotelsDbBundle\Service\Money\Price\RoomPrice
 */
class Tax implements TaxInterface
{
    /**
     * @var bool
     */
    private $included;

    /**
     * @var MoneyInterface
     */
    private $rate;

    /**
     * @var string
     */
    private $type;

    /**
     * Tax constructor.
     *
     * @param bool $included
     * @param MoneyInterface $rate
     * @param string $type
     */
    public function __construct(bool $included, MoneyInterface $rate, string $type = self::TYPE_PAY_IN_HOTEL)
    {
        $this->included = $included;
        $this->rate = $rate;
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isIncluded(): bool
    {
        return $this->included;
    }

    /**
     * @return MoneyInterface
     */
    public function getRate(): MoneyInterface
    {
        return $this->rate;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param array $data
     * @return TaxInterface
     */
    public static function createFromArray(array $data): TaxInterface
    {
        return new self(
            $data['included'],
            Money::createFromArray($data['rate']),
            $data['type']
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'included' => $this->isIncluded(),
            'rate' => $this->getRate()->toArray(),
            'type' => $this->getType(),
        ];
    }
}