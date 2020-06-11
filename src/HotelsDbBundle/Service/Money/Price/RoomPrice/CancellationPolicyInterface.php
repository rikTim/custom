<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\RoomPrice;


use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Interface CancellationPolicyInterface
 *
 * @package Apl\HotelsDbBundle\Service\Money\Price\RoomPrice
 */
interface CancellationPolicyInterface
{
    /**
     * @param array $data
     * @return CancellationPolicyInterface
     */
    public static function createFromArray(array $data): CancellationPolicyInterface;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return \DateTimeImmutable
     * @Groups("public")
     */
    public function getFrom(): \DateTimeImmutable;

    /**
     * @return MoneyInterface
     * @Groups("money")
     */
    public function getPenalty(): MoneyInterface;
}