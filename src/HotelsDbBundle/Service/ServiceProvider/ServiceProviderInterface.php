<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\Service\HotelFilter\Collection\FilterCollectionInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDTO;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportScenario;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Interface ServiceProviderInterface
 * @package Apl\HotelsDbBundle\Service\ServiceProvider
 */
interface ServiceProviderInterface
{
    /**
     * @return ServiceProviderAlias
     */
    public function getServiceProviderAlias(): ServiceProviderAlias;

    /**
     * @return ImportScenario
     * @throws \Apl\HotelsDbBundle\Exception\LogicException
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     */
    public function getImportStaticDataScenario(): ImportScenario;

    /**
     * TODO: Подумать как можно избавиться от промисов
     *
     * @param ServiceProviderReferenceInterface[] $hotelReferences
     * @param FilterCollectionInterface $filterCollection
     * @return PromiseInterface
     */
    public function getAvailablePrices(array $hotelReferences, FilterCollectionInterface $filterCollection): PromiseInterface;

    /**
     * @param string $rateKey
     * @return ImportDTO|null
     */
    public function checkRate(string $rateKey): ?ImportDTO;
}