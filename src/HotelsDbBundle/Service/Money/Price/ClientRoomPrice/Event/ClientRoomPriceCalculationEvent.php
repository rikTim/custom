<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\Event;


use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\ClientRoomPriceInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ClientRoomPriceCalculationEvent
 *
 * @package Apl\HotelsDbBundle\Service\Money\Price\Markup\Event
 */
class ClientRoomPriceCalculationEvent extends Event
{
    public const NAME = 'client_room_price_calculation';

    /**
     * @var ClientRoomPriceInterface[]
     */
    private $notCalculatedPrices;

    /**
     * @var ClientRoomPriceInterface[]
     */
    private $calculatedPrices;

    /**
     * ClientRoomPriceCalculationEvent constructor.
     *
     * @param ClientRoomPriceInterface ...$clientRoomPrices
     */
    public function __construct(ClientRoomPriceInterface ...$clientRoomPrices)
    {
        $this->notCalculatedPrices = [];
        foreach ($clientRoomPrices as $roomPrice) {
            $this->notCalculatedPrices[spl_object_hash($roomPrice)] = $roomPrice;
        }
    }

    /**
     * @return ClientRoomPriceInterface[]
     */
    public function getNotCalculatedPrices(): array
    {
        return $this->notCalculatedPrices;
    }

    /**
     * @return ClientRoomPriceInterface[]
     */
    public function getCalculatedPrices(): array
    {
        return $this->calculatedPrices;
    }

    /**
     * @return ClientRoomPriceInterface[]
     */
    public function getAllPrices(): array
    {
        return array_merge($this->getNotCalculatedPrices(), $this->getCalculatedPrices());
    }

    /**
     * @param ClientRoomPriceInterface $roomPrice
     * @return ClientRoomPriceCalculationEvent
     */
    public function markAsCalculated(ClientRoomPriceInterface $roomPrice): self
    {
        $priceKey = spl_object_hash($roomPrice);
        if (!$this->notCalculatedPrices[$priceKey]) {
            throw new InvalidArgumentException('This room price not provide in calculation event');
        }

        unset($this->notCalculatedPrices[$priceKey]);
        $this->calculatedPrices[$priceKey] = $roomPrice;

        return $this;
    }
}