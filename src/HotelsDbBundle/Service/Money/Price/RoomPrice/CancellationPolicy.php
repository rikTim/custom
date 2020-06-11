<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\RoomPrice;


use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;

/**
 * Class CancellationPolicy
 *
 * @package Apl\HotelsDbBundle\Service\Money\Price\RoomPrice
 */
class CancellationPolicy implements CancellationPolicyInterface
{
    /**
     * @var \DateTimeImmutable
     */
    protected $from;

    /**
     * @var MoneyInterface
     */
    protected $penalty;

    /**
     * CancellationPolicy constructor.
     *
     * @param \DateTimeImmutable $from
     * @param MoneyInterface $penalty
     */
    public function __construct(\DateTimeImmutable $from, MoneyInterface $penalty)
    {
        $this->from = $from;
        $this->penalty = $penalty;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getFrom(): \DateTimeImmutable
    {
        return $this->from;
    }

    /**
     * @return MoneyInterface
     */
    public function getPenalty(): MoneyInterface
    {
        return $this->penalty;
    }


    /**
     * @param array $data
     * @return CancellationPolicyInterface
     */
    public static function createFromArray(array $data): CancellationPolicyInterface
    {
        return new self(
            \DateTimeImmutable::createFromFormat(\DateTime::RFC3339, $data['from']),
            Money::createFromArray($data['penalty'])
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'from' => $this->getFrom()->format(\DateTime::RFC3339),
            'penalty' => $this->getPenalty()->toArray(),
        ];
    }
}