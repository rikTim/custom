<?php


namespace Apl\HotelsDbBundle\EventSubscriber;


use Apl\HotelsDbBundle\Service\HotelFilter\Collection\FilterCollectionInterface;
use Apl\HotelsDbBundle\Service\HotelFilter\HotelFilterService;
use Knp\Component\Pager\Event\ItemsEvent;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PaginateHotelListEventSubscriber
 *
 * @package Apl\HotelsDbBundle\EventSubscriber
 */
class PaginateHotelListEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var HotelFilterService
     */
    private $hotelFilterService;

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.items' => ['items', -1],
        ];
    }


    /**
     * PaginateHotelListEventSubscriber constructor.
     *
     * @param HotelFilterService $hotelFilterService
     */
    public function __construct(HotelFilterService $hotelFilterService)
    {
        $this->hotelFilterService = $hotelFilterService;
    }

    /**
     * @param ItemsEvent $event
     * @throws InvalidArgumentException
     */
    public function items(ItemsEvent $event)
    {
        if (\is_object($event->target) && $event->target instanceof FilterCollectionInterface) {
            $event->stopPropagation();

            $collection = $this->hotelFilterService->getFilteredHotelList($event->target, $event->getOffset(), $event->getLimit());
            $event->items = $collection->getIterator();
            $event->count = $collection->getTotalResults();
        }
    }
}