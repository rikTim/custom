<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice;


use Apl\HotelsDbBundle\Service\EventDispatcherAwareTrait;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\Event\ClientRoomPriceCalculationEvent;

/**
 * Class ClientPriceService
 *
 * @package Apl\HotelsDbBundle\Service\Markup
 */
class ClientPriceService
{
    use EventDispatcherAwareTrait,
        LoggerAwareTrait;

    /**
     * @param ClientRoomPriceInterface ...$clientRoomPrices
     * @return \Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\RoomPriceInterface[]
     */
    public function calculateClientPrice(ClientRoomPriceInterface ...$clientRoomPrices): array
    {
        $event = new ClientRoomPriceCalculationEvent(...$clientRoomPrices);

        $this->eventDispatcher->dispatch(ClientRoomPriceCalculationEvent::NAME, $event);

        foreach ($event->getNotCalculatedPrices() as $roomPrice) {
            $this->logger->error('Some client prices not calculated', [
                'roomPrice.hotel.id' => $roomPrice->getHotel()->getId(),
                'roomPrice.room.id' => $roomPrice->getRoom() ? $roomPrice->getRoom()->getId() : null,
                'roomPrice.serviceProviderReference' => (string)$roomPrice->getServiceProviderReference(),
                'roomPrice.rateNet' => (string)$roomPrice->getRateNet(),
                'roomPrice.rateClient' => (string)$roomPrice->getRateClient(),
            ]);
        }

        return $event->getCalculatedPrices();
    }
}