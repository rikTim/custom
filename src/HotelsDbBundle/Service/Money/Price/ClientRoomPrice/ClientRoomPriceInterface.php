<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice;


use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\RoomPriceInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Interface ClientRoomPriceInterface
 *
 * @package HotelsBundle\Service\Money\Price\ClientRoomPrice
 */
interface ClientRoomPriceInterface extends RoomPriceInterface
{
    /**
     * @return MoneyInterface
     * @Groups("public")
     */
    public function getRateClient(): MoneyInterface;
}