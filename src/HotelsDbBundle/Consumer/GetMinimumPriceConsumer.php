<?php


namespace Apl\HotelsDbBundle\Consumer;


use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\EmbeddableServiceProviderReference;
use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Entity\Money\Price\MinimumPrice;
use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\AdultQuantityFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\BoardFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\ChildrenAgesFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\DatesFilterEventSubscriber;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\Money\Price\MinimumPrice\MinimumPriceMessage;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ObjectDataManipulatorAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDTO;
use Apl\HotelsDbBundle\Service\ServiceProvider\PriceFinder\PriceFinder;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ReferencedEntityResolverAwareTrait;
use Apl\PriceBundle\Service\CurrencyConverterAwareTrait;
use Apl\RabbitBundle\Service\AMQP\AbstractConsumer;
use Apl\RabbitBundle\Service\AMQP\MessageInterface;

/**
 * Class ImportCollectionConsumer
 *
 * @package Apl\HotelsDbBundle\Consumer
 */
class GetMinimumPriceConsumer extends AbstractConsumer
{
    use EntityManagerAwareTrait,
        ReferencedEntityResolverAwareTrait,
        CurrencyConverterAwareTrait,
        ObjectDataManipulatorAwareTrait;

    /**
     * @var PriceFinder
     */
    private $priceFinder;

    /**
     * ImportCollectionConsumer constructor.
     *
     * @param PriceFinder $priceFinder
     */
    public function __construct(PriceFinder $priceFinder)
    {
        $this->priceFinder = $priceFinder;
    }

    /**
     * @return string
     */
    protected function getMessageClassName(): string
    {
        return MinimumPriceMessage::class;
    }

    /**
     * @param \Apl\HotelsDbBundle\Service\Money\Price\MinimumPrice\MinimumPriceMessage|MessageInterface $message
     * @return int
     */
    protected function run(MessageInterface $message): int
    {
        $this->entityManager->getConnection()->close();

        /** @var Hotel[] $hotels */
        $hotels = $this->entityManager->getRepository(Hotel::class)->findBy(['id' => $message->getHotelIds()]);
        $this->assertFindListCount(Hotel::class, $message->getHotelIds(), $hotels);

        $filterCollection = $message->getFilterCollection();

        /** @var \SplObjectStorage|ImportDTO[][] $availablePrices */
        $availablePrices = $this->priceFinder->getAvailablePrices($hotels, $filterCollection);

        // Подготавливаем связанные данные и находим минимальные цены по группам
        $pricesAggregatedByBoard = [];
        $dtoToSpAliasMap = [];
        /** @var ServiceProviderAlias $serviceProviderAlias */
        foreach ($availablePrices as $serviceProviderAlias) {
            $priceResponses = $availablePrices[$serviceProviderAlias];

            $this->referencedEntityResolver->resolveExternalReferences(
                MinimumPrice::class,
                $priceResponses,
                $serviceProviderAlias
            );

            foreach ($priceResponses as $importDTO) {
                $dtoToSpAliasMap[$importDTO->getExternalReference()] = $serviceProviderAlias;
                $hotelId = $importDTO['hotel']->getId();
                $boardId = $importDTO['board']->getId();

                if (!isset($pricesAggregatedByBoard[$hotelId][$boardId])) {
                    $pricesAggregatedByBoard[$hotelId][$boardId] = $importDTO;
                } else {
                    /** @var \Money\Money $currentMoney */
                    $currentMoney = $currentComparableMoney = $importDTO['rateNet']->getMoney();

                    /** @var \Money\Money $previousMoney */
                    $previousMoney = $pricesAggregatedByBoard[$hotelId][$boardId]['rateNet']->getMoney();

                    if (!$previousMoney->isSameCurrency($currentMoney)) {
                        $currentComparableMoney = $this->currencyConverter->convert($currentMoney, $previousMoney->getCurrency());
                    }

                    if ($previousMoney->compare($currentComparableMoney) > 0) {
                        $pricesAggregatedByBoard[$hotelId][$boardId] = $importDTO;
                    }
                }
            }
        }

        $repository = $this->entityManager->getRepository(MinimumPrice::class);
        /** @var ImportDTO $importDTO */
        if ($pricesAggregatedByBoard) {
            foreach (array_unique(array_merge(...$pricesAggregatedByBoard)) as $importDTO) {
                foreach ($importDTO['dailyRates'] as $offset => $dailyRate) {
                    $entity = new MinimumPrice();
                    $this->objectDataManipulator->hydrate($entity, $importDTO);

                    $offset--;
                    if ($offset > 0) {
                        $entity->setCheckIn($entity->getCheckIn()->modify('+' . $offset . ' days'));
                    }

                    $entity->setCheckOut($entity->getCheckIn()->modify('+1 day'));
                    $entity->setServiceProviderReference(
                        new EmbeddableServiceProviderReference(
                            $dtoToSpAliasMap[$importDTO->getExternalReference()],
                            $importDTO->getExternalReference()
                        )
                    );

                    $entity->getRateNet()->setAmount($dailyRate);

                    if ($existEntity = $repository->findExistByUniqueConstraint($entity)) {
                        if (!$this->objectDataManipulator->compare($existEntity, $entity)) {
                            $this->objectDataManipulator->hydrate($existEntity, $entity);
                            $this->entityManager->persist($existEntity);
                        }
                    } else {
                        $this->entityManager->persist($entity);
                    }
                }
            }
        }

        // Собираем пустые цены
        $boardIds = $filterCollection->get(BoardFilterEventSubscriber::FILTER_KEY)->getValue() ?? [];
        if ($boardIds) {
            $boards = $this->entityManager->getRepository(Board::class)->findBy(['id' => $boardIds]);
            $this->assertFindListCount(Board::class, $boardIds, $boards);
        }

        if (!isset($boards)) { // This is optimal, repository can return empty array
            $boards = [null];
        }

        $childrenAges = $filterCollection->get(ChildrenAgesFilterEventSubscriber::FILTER_KEY)->getValue() ?? [];
        $baseEntity = new MinimumPrice();
        $baseEntity
            ->setRateNet(new Money(0, Money::EMPTY_CURRENCY))
            ->setChildren(\count($childrenAges))->setChildrenAges($childrenAges)
            ->setAdults($filterCollection->get(AdultQuantityFilterEventSubscriber::FILTER_KEY)->getValue())
            ->setRoomQuantity(0)
            ->setServiceProviderReference(new EmbeddableServiceProviderReference());

        $startDate = $filterCollection->get(DatesFilterEventSubscriber::FILTER_KEY_START)->getValue();
        $endDate = $filterCollection->get(DatesFilterEventSubscriber::FILTER_KEY_END)->getValue();


        foreach ($hotels as $hotel) {
            // Если есть хоть один тип питания - остальные проверять нельзя, т.к. нет гарантий что их нет у провайдера
            if (!isset($pricesAggregatedByBoard[$hotel->getId()])) {
                foreach ($boards as $board) {
                    $entity = clone $baseEntity;
                    $entity->setHotel($hotel)
                        ->setBoard($board)
                        ->setCheckIn($startDate)
                        ->setCheckOut($endDate);

                    if ($existEntity = $repository->findExistByUniqueConstraint($entity)) {
                        if (!$this->objectDataManipulator->compare($existEntity, $entity)) {
                            $this->objectDataManipulator->hydrate($existEntity, $entity);
                            $this->entityManager->persist($existEntity);
                        }
                    } else {
                        $this->entityManager->persist($entity);
                    }
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->referencedEntityResolver->clearSelfReferences();

        return self::MSG_ACK;
    }
}