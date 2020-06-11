<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\MinimumPrice;


use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Entity\Money\Price\MinimumPrice;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\AdultQuantityFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\BoardFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\ChildrenAgesFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\CurrencyFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\DatesFilterEventSubscriber;
use Apl\HotelsDbBundle\Exception\FilterNotFoundException;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\HotelFilter\Collection\FilterCollectionInterface;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\ClientPriceService;
use Apl\HotelsDbBundle\Service\Money\Price\Predictor\PriceRangePredictor;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\RoomPriceInterface;
use Apl\PriceBundle\Service\CurrencyConverterAwareTrait;
use Apl\RabbitBundle\Service\ProducerManagerAwareTrait;

/**
 * Class MinimumPriceService
 *
 * @package Apl\HotelsDbBundle\Service\Price
 */
class MinimumPriceService
{
    use EntityManagerAwareTrait,
        ProducerManagerAwareTrait,
        CurrencyConverterAwareTrait;

    /**
     * @var \Apl\HotelsDbBundle\Service\Money\Price\Predictor\PriceRangePredictor
     */
    private $priceRangePredictor;

    /**
     * @var ClientPriceService
     */
    private $clientPriceService;

    /**
     * MinimumPriceService constructor.
     *
     * @param \Apl\HotelsDbBundle\Service\Money\Price\Predictor\PriceRangePredictor $priceRangePredictor
     * @param ClientPriceService $clientPriceService
     */
    public function __construct(
        PriceRangePredictor $priceRangePredictor,
        ClientPriceService $clientPriceService
    )
    {
        $this->priceRangePredictor = $priceRangePredictor;
        $this->clientPriceService = $clientPriceService;
    }

    /**
     * @param Hotel[] $hotels
     * @param FilterCollectionInterface $filterCollection
     * @param bool $reNewPrice
     * @return MoneyInterface[]
     * @throws \Exception
     */
    public function getHotelsMinPrice(array $hotels, FilterCollectionInterface $filterCollection, bool $reNewPrice = true): array
    {
        try {
            $startDate = $filterCollection->get(DatesFilterEventSubscriber::FILTER_KEY_START)->getValue();
            $endDate = $filterCollection->get(DatesFilterEventSubscriber::FILTER_KEY_END)->getValue();
            if (!$startDate || !$endDate) {
                throw new InvalidArgumentException('Empty value for date filter');
            }

            $adultQuantity = (int)$filterCollection->get(AdultQuantityFilterEventSubscriber::FILTER_KEY)->getValue();
            if (!$adultQuantity) {
                throw new InvalidArgumentException('Empty value for adult filter');
            }

            $childrenAges = $filterCollection->get(ChildrenAgesFilterEventSubscriber::FILTER_KEY)->getValue() ?? [];
            $boards = $filterCollection->get(BoardFilterEventSubscriber::FILTER_KEY)->getValue() ?? [];
            $currency = $filterCollection->get(CurrencyFilterEventSubscriber::FILTER_KEY)->getValue() ?? 'UAH';
        } catch (FilterNotFoundException $e) {
            throw new InvalidArgumentException('Missing required filters for get minimum prices', $e->getCode(), $e);
        }

        /** @var MinimumPrice[][] $minimumPrices */
        $minimumPrices = $this->entityManager->getRepository(MinimumPrice::class)
            ->findMinimumPricesForHotelList(
                $hotels,
                $startDate,
                $endDate,
                $adultQuantity,
                \count($childrenAges),
                $boards
            );

        $response = [];
        $reNewHotels = [];

        /** @var MinimumPrice[][] $aggregatedMinimumPrices */
        $aggregatedMinimumPrices = [];

        foreach ($hotels as $hotel) {
            // Отели по которым ничего не нашли ставим в очередь на получение цен
            if (!isset($minimumPrices[$hotel->getId()])) {
                $reNewHotels[] = $hotel->getId();
            } else {
                foreach ($minimumPrices[$hotel->getId()] as $minimumPrice) {
                    if ($minimumPrice->getRoomQuantity() === 0) {
                        // Если будут еще цены на отель они перезапишутся в ответе после агрегации
                        $response[$hotel->getId()] = $minimumPrice->getPredictableMoney();
                    } else {
                        // Convert to client currency
                        $minimumPrice->getRateNet()->setMoney(
                            $this->currencyConverter->convert($minimumPrice->getRateNet()->getMoney(), $currency)
                        );

                        $key = $minimumPrice->getPredictableDate()->format('Ymd');

                        if (
                            !isset($aggregatedMinimumPrices[$hotel->getId()][$key])
                            || $aggregatedMinimumPrices[$hotel->getId()][$key]->getRateNet()->compare($minimumPrice->getRateNet()) > 0
                        ) {
                            $aggregatedMinimumPrices[$hotel->getId()][$key] = $minimumPrice;
                        }
                    }
                }
            }
        }

        if ($aggregatedMinimumPrices) {
            $this->clientPriceService->calculateClientPrice(...array_merge(...$aggregatedMinimumPrices));

            $endPricedDate = $endDate->modify('-1 day');

            foreach ($aggregatedMinimumPrices as $hotelId => $roomPrices) {
                $response[$hotelId] = $this->calculateAvgNightCost(
                    // Скалируем недостающие цены
                    $this->priceRangePredictor->predictNightCost($startDate, $endPricedDate, $roomPrices)
                );
            }
        }

        if ($reNewPrice && $reNewHotels) {
            $this->producerManager->publishOnce(new MinimumPriceMessage(array_unique($reNewHotels), $filterCollection));
        }

        return $response;
    }

    /**
     * @param RoomPriceInterface[] $prices
     * @return \Apl\HotelsDbBundle\Service\Money\MoneyInterface
     */
    public function calculateAvgNightCost(array $prices): MoneyInterface
    {
        $avgCurrency = new \Money\Currency(current($prices)->getRateNet()->getCurrency());
        $avg = new \Money\Money(0, $avgCurrency);
        foreach ($prices as $price) {
            $avg = $avg->add($price->getRateNet()->getMoney());
        }

        return (new Money())->setMoney($avg->divide(\count($prices)));
    }
}