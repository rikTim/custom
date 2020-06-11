<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\MinimumPrice;


use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\AdultQuantityFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\BoardFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\ChildrenAgesFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\DatesFilterEventSubscriber;
use Apl\HotelsDbBundle\Service\HotelFilter\Collection\FilterCollectionInterface;
use Apl\HotelsDbBundle\Service\HotelFilter\Filter\FilterList;
use Apl\RabbitBundle\Service\AMQP\SerializableMessage;

/**
 * Class MinimumPriceMessage
 *
 * @package Apl\HotelsDbBundle\Service\Price
 */
class MinimumPriceMessage extends SerializableMessage
{
    public const ROUTING_KEY = 'get_minimum_price';

    /**
     * ImportCollectionMessage constructor.
     *
     * @param array $hotelIds
     * @param FilterCollectionInterface $filterCollection
     */
    public function __construct(array $hotelIds, FilterCollectionInterface $filterCollection)
    {
        $filterSlice = $filterCollection->filter([
            DatesFilterEventSubscriber::FILTER_KEY_START,
            DatesFilterEventSubscriber::FILTER_KEY_END,
            AdultQuantityFilterEventSubscriber::FILTER_KEY,
            ChildrenAgesFilterEventSubscriber::FILTER_KEY,
            BoardFilterEventSubscriber::FILTER_KEY
        ]);

        /** @var FilterList $boardFilter */
        $boardFilter = $filterSlice->get(BoardFilterEventSubscriber::FILTER_KEY);

        // Незачем отправлять неотмеченные фильтры в ребит
        $boardFilter->clearNotChecked();

        parent::__construct(
            self::ROUTING_KEY,
            ['hotel_ids' => $hotelIds, 'filter' => $filterSlice]
        );
    }

    /**
     * @return int[]
     */
    public function getHotelIds(): array
    {
        return $this->body['hotel_ids'];
    }

    /**
     * @return FilterCollectionInterface
     */
    public function getFilterCollection(): FilterCollectionInterface
    {
        return $this->body['filter'];
    }
}