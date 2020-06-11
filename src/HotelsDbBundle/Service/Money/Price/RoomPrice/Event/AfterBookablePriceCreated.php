<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\Event;


use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\RoomPriceInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AfterBookablePriceCreated
 *
 * @package Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\Event
 */
class AfterBookablePriceCreated extends Event
{
    public const NAME = 'after_bookable_price_created';

    /**
     * @var RoomPriceInterface
     */
    private $roomPrice;

    /**
     * AfterBookablePriceCreated constructor.
     *
     * @param RoomPriceInterface $roomPrice
     */
    public function __construct(RoomPriceInterface $roomPrice)
    {
        $this->roomPrice = $roomPrice;
    }

    /**
     * @return RoomPriceInterface
     */
    public function getRoomPrice(): RoomPriceInterface
    {
        return $this->roomPrice;
    }
}