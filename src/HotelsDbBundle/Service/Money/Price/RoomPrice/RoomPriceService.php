<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\RoomPrice;


use Apl\HotelsDbBundle\Entity\EmbeddableServiceProviderReference;
use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Money\Price\AbstractClientRoomPrice;
use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\AdultQuantityFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\BoardFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\ChildrenAgesFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\CurrencyFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\DatesFilterEventSubscriber;
use Apl\HotelsDbBundle\Exception\FilterNotFoundException;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Exception\ServiceProviderResponseException;
use Apl\HotelsDbBundle\Service\CacheAwareTrait;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\EventDispatcherAwareTrait;
use Apl\HotelsDbBundle\Service\HotelFilter\Collection\FilterCollectionInterface;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\ClientPriceService;
use Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\ClientRoomPriceInterface;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\Event\AfterBookablePriceCreated;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ObjectDataManipulatorAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDTO;
use Apl\HotelsDbBundle\Service\ServiceProvider\PriceFinder\PriceFinder;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ReferencedEntityResolverAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ServiceProviderManagerAwareTrait;
use Apl\PriceBundle\Service\CurrencyConverterAwareTrait;

/**
 * Class RoomPriceService
 *
 * @package Apl\HotelsDbBundle\Service\Price
 */
class RoomPriceService
{
    use CacheAwareTrait,
        EntityManagerAwareTrait,
        ReferencedEntityResolverAwareTrait,
        CurrencyConverterAwareTrait,
        ObjectDataManipulatorAwareTrait,
        LoggerAwareTrait,
        ServiceProviderManagerAwareTrait,
        EventDispatcherAwareTrait;

    private const CACHE_PREFIX = 'hotels_db.hotel_room_price.';

    /**
     * @var PriceFinder
     */
    private $priceFinder;

    /**
     * @var ClientPriceService
     */
    private $clientPrice;

    /**
     * RoomPriceService constructor.
     *
     * @param PriceFinder $priceFinder
     */
    public function __construct(PriceFinder $priceFinder, ClientPriceService $clientPrice)
    {
        $this->priceFinder = $priceFinder;
        $this->clientPrice = $clientPrice;
    }

    /**
     * @param Hotel $hotel
     * @param FilterCollectionInterface $filterCollection
     * @return RoomPriceDTO[][]
     */
    public function getRoomPrices(Hotel $hotel, FilterCollectionInterface $filterCollection): array
    {
        try {
            $cacheKey = $this->generateCacheKey($hotel, $filterCollection);
            $currency = $filterCollection->get(CurrencyFilterEventSubscriber::FILTER_KEY)->getValue() ?? 'UAH';
        } catch (FilterNotFoundException $e) {
            throw new InvalidArgumentException('Missing required filters for get minimum prices', $e->getCode(), $e);
        }

        // Получаем сгруппированные цены на номера в отеле
        try {
            $pricesAggregatedByRoomBoard = $this->getCachedValue($cacheKey, function () use ($hotel, $filterCollection) : array {
                /** @var \SplObjectStorage|ServiceProviderAlias[] $availablePrices */
                $availablePrices = $this->priceFinder->getAvailablePrices([$hotel], $filterCollection);

                /** @var RoomPriceDTO[][] $pricesAggregatedByRoomBoard */
                $pricesAggregatedByRoomBoard = [];
                foreach ($availablePrices as $serviceProviderAlias) {
                    /** @var ImportDTO[] $priceResponses */
                    $priceResponses = $availablePrices[$serviceProviderAlias];

                    $this->referencedEntityResolver->resolveExternalReferences(
                        AbstractClientRoomPrice::class,
                        $priceResponses,
                        $serviceProviderAlias
                    );

                    foreach ($priceResponses as $importDTO) {
                        $roomPriceDTO = new RoomPriceDTO();
                        $this->objectDataManipulator->hydrate($roomPriceDTO, $importDTO);

                        $roomPriceDTO
                            ->setCreated(new \DateTimeImmutable())
                            ->setServiceProviderReference(
                                new EmbeddableServiceProviderReference(
                                    $serviceProviderAlias,
                                    $importDTO->getExternalReference()
                                )
                            );

                        $roomId = $roomPriceDTO->getRoomId();
                        $boardId = $roomPriceDTO->getBoardId();
                        if (!isset($pricesAggregatedByRoomBoard[$roomId][$boardId])) {
                            $pricesAggregatedByRoomBoard[$roomId][$boardId] = $roomPriceDTO;
                        } else {
                            /** @var \Money\Money $currentMoney */
                            $currentMoney = $currentComparableMoney = $importDTO['rateNet']->getMoney();

                            /** @var \Money\Money $previousMoney */
                            $previousMoney = $pricesAggregatedByRoomBoard[$roomId][$boardId]->getRateNet()->getMoney();

                            if (!$previousMoney->isSameCurrency($currentMoney)) {
                                $currentComparableMoney = $this->currencyConverter->convert($currentMoney, $previousMoney->getCurrency());
                            }

                            if ($previousMoney->compare($currentComparableMoney) > 0) {
                                $pricesAggregatedByRoomBoard[$roomId][$boardId] = $roomPriceDTO;
                            }
                        }
                    }
                }

                return $pricesAggregatedByRoomBoard;
            }, 1500);
        } catch (ServiceProviderResponseException $exception) {
            $this->setCachedValue($cacheKey, [], 30);
            $this->logger->emergency('Service provider response error: {message}', ['message' => $exception->getMessage()]);
            return [];
        }

        if ($pricesAggregatedByRoomBoard) {
            $priceList = array_merge(...$pricesAggregatedByRoomBoard);

            /** @var RoomPriceDTO $roomPriceDTO */
            foreach ($priceList as $roomPriceDTO) {
                $roomPriceDTO->initialize($this->entityManager);
            }

            $this->clientPrice->calculateClientPrice(...$priceList);

            // Конвертируем в нужную валюту
            foreach ($pricesAggregatedByRoomBoard as $pricesAggregatedByBoard) {

                /** @var RoomPriceDTO $roomPriceDTO */
                foreach ($pricesAggregatedByBoard as $roomPriceDTO) {
                    $this->convertToCurrency($roomPriceDTO, $currency);
                }
            }
        }

        return $pricesAggregatedByRoomBoard;
    }

    /**
     * @param EmbeddableServiceProviderReference $reference
     * @param string $currency
     * @return RoomPriceDTO|null
     * @throws \Doctrine\ORM\ORMException
     */
    public function checkRate(EmbeddableServiceProviderReference $reference, string $currency): ?RoomPriceDTO
    {
        $roomPriceDTO = null;
        $importDTO = $this->serviceProviderManager
            ->getServiceProvider($reference->getAlias())
            ->checkRate($reference->getReference());

        if ($importDTO) {
            $this->referencedEntityResolver->resolveExternalReferences(
                AbstractClientRoomPrice::class,
                [$importDTO],
                $reference->getAlias()
            );

            $roomPriceDTO = new RoomPriceDTO();
            $this->objectDataManipulator->hydrate($roomPriceDTO, $importDTO);

            $roomPriceDTO
                ->setCreated(new \DateTimeImmutable())
                ->setServiceProviderReference(
                    new EmbeddableServiceProviderReference(
                        $reference->getAlias(),
                        $importDTO->getExternalReference()
                    )
                );


            $roomPriceDTO->initialize($this->entityManager);
            $this->clientPrice->calculateClientPrice($roomPriceDTO);
            $this->convertToCurrency($roomPriceDTO, $currency);
        }

        return $roomPriceDTO;
    }

    /**
     * @param ClientRoomPriceInterface $existPrice
     * @param FilterCollectionInterface $filterCollection
     * @return RoomPriceDTO|null
     */
    public function getBookablePrice(ClientRoomPriceInterface $existPrice, FilterCollectionInterface $filterCollection): ?RoomPriceDTO
    {
        try {
            $currency = $filterCollection->get(CurrencyFilterEventSubscriber::FILTER_KEY)->getValue() ?? 'UAH';
        } catch (FilterNotFoundException $e) {
            throw new InvalidArgumentException('Missing required filters for get bookable price', $e->getCode(), $e);
        }

        if (!$existPrice->getRoom()) {
            throw new InvalidArgumentException('Missing room for get bookable price');
        }

        if (!$existPrice->getBoard()) {
            throw new InvalidArgumentException('Missing board for get bookable price');
        }

        if (
            $existPrice->getRateType() === RoomPriceInterface::RATE_TYPE_BOOKABLE
            && $existPrice->getUpdated() > new \DateTimeImmutable('-25 minute')
        ) {$bookablePrice = $this->checkRate($existPrice->getServiceProviderReference(), $currency);

        } else {
            $bookablePrice = null;
            $pricesAggregatedByRoomBoard = $this->getRoomPrices($existPrice->getRoom()->getHotel(), $filterCollection);

            if (isset($pricesAggregatedByRoomBoard[$existPrice->getRoom()->getId()][$existPrice->getBoard()->getId()])) {
                $bookablePrice = $pricesAggregatedByRoomBoard[$existPrice->getRoom()->getId()][$existPrice->getBoard()->getId()];
                $rateClient =  $bookablePrice->getRateClient();
                $rateClient->setAmount($rateClient->getRoundAmount());
                $cancellationPolicies =  $bookablePrice->getCancellationPolicies();
                foreach ($cancellationPolicies as $policy)
                {
                    $penalty = $policy->getPenalty();
                    $penalty->setAmount($penalty->getRoundAmount());
                }
                if ($bookablePrice->getRateType() !== RoomPriceInterface::RATE_TYPE_BOOKABLE) {
                    $bookablePrice = $this->checkRate($bookablePrice->getServiceProviderReference(), $currency);
                }
            }
        }

        $this->eventDispatcher->dispatch(AfterBookablePriceCreated::NAME, new AfterBookablePriceCreated($bookablePrice));

        return $bookablePrice;
    }

    /**
     * @param Hotel $hotel
     * @param FilterCollectionInterface $filterCollection
     * @return string
     */
    private function generateCacheKey(Hotel $hotel, FilterCollectionInterface $filterCollection): string
    {
        /** @var \DateTimeImmutable $startDate */
        $startDate = $filterCollection->get(DatesFilterEventSubscriber::FILTER_KEY_START)->getValue();

        /** @var \DateTimeImmutable $endDate */
        $endDate = $filterCollection->get(DatesFilterEventSubscriber::FILTER_KEY_END)->getValue();
        if (!$startDate || !$endDate) {
            throw new InvalidArgumentException('Empty value for date filter');
        }

        $adultQuantity = (int)$filterCollection->get(AdultQuantityFilterEventSubscriber::FILTER_KEY)->getValue();
        if (!$adultQuantity) {
            throw new InvalidArgumentException('Empty value for adult filter');
        }

        $childrenAges = $filterCollection->has(ChildrenAgesFilterEventSubscriber::FILTER_KEY)
            ? $filterCollection->get(ChildrenAgesFilterEventSubscriber::FILTER_KEY)->getValue() ?? []
            : [];

        $boards = $filterCollection->has(BoardFilterEventSubscriber::FILTER_KEY)
            ? $filterCollection->get(BoardFilterEventSubscriber::FILTER_KEY)->getValue() ?? []
            : [];

        return self::CACHE_PREFIX . 'HOTEL_' . $hotel->getId() . '.'
            . implode('|', [
                $adultQuantity,
                \count($childrenAges),
                implode(',', $boards),
                $startDate->format('Ymd'),
                $endDate->format('Ymd'),
            ]);
    }

    /**
     * @param RoomPriceDTO $roomPriceDTO
     * @param string $currency
     */
    private function convertToCurrency(RoomPriceDTO $roomPriceDTO, string $currency): void
    {
        /** @var MoneyInterface[] $allMoney */
        $allMoney = array_merge(
            [$roomPriceDTO->getRateClient()],

            array_map(function (CancellationPolicyInterface $cancellationPolicy) {
                return $cancellationPolicy->getPenalty();
            }, $roomPriceDTO->getCancellationPolicies()),

            array_map(function (TaxInterface $tax) {
                return $tax->getRate();
            }, $roomPriceDTO->getTaxes())
        );

        foreach ($allMoney as $money) {
            if ($money->isAvailable() && mb_strtoupper($money->getCurrency()) !== $currency) {
                $money->setMoney($this->currencyConverter->convert($money->getMoney(), $currency));
            }
        }
    }
}