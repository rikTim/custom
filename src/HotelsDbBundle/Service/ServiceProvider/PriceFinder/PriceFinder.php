<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\PriceFinder;


use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Hotel\HotelSPReference;
use Apl\HotelsDbBundle\Exception\ServiceProviderResponseException;
use Apl\HotelsDbBundle\Service\HotelFilter\Collection\FilterCollectionInterface;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDTO;
use Apl\HotelsDbBundle\Service\ServiceProvider\ServiceProviderManagerAwareTrait;
use GuzzleHttp\Promise\EachPromise;


/**
 * Class PriceFinder
 *
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\PriceFinder
 */
class PriceFinder
{
    use ServiceProviderManagerAwareTrait,
        LoggerAwareTrait;

    /**
     * @param Hotel[] $hotels
     * @param FilterCollectionInterface $filterCollection
     * @return \SplObjectStorage|ImportDTO[]
     */
    public function getAvailablePrices(array $hotels, FilterCollectionInterface $filterCollection): \SplObjectStorage
    {
        /** @var HotelSPReference[][] $aggregated */
        $aggregated = [];
        $spAliases = [];
        foreach ($hotels as $hotel) {
            foreach ($hotel->getServiceProviderReferences() as $spReference) {
                $spAlias = $spReference->getAlias();
                $aggregated[(string)$spAlias][] = $spReference;
                $spAliases[(string)$spAlias] = $spAlias;
            }
        }

        $promises = [];
        $prices = [];
        $errorCount = 0;

        foreach ($aggregated as $serviceProviderName => $references) {
            try {
                $serviceProvider = $this->serviceProviderManager->getServiceProvider($serviceProviderName);
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning(
                    'Invalid hotel reference: ' . $e->getMessage(),
                    [
                        'hotelIds' => array_reduce($references, function(array $acc, HotelSPReference $reference) {
                            $acc[] = $reference->getEntity()->getId();
                            return $acc;
                        }, [])
                    ]
                );

                continue;
            }

            $promises[] = $serviceProvider->getAvailablePrices($references, $filterCollection)
                ->then(
                    function($response) use (&$prices, $serviceProviderName) {
                        $prices[$serviceProviderName][] = $response;
                    },
                    function($reason) use (&$errorCount, $serviceProviderName) {
                        $errorCount++;
                        if ($reason instanceof \Throwable) {
                            do {
                                $this->logger->error('Error on get available prices: {serviceProvider}', [
                                    'exception' => $reason,
                                    'serviceProvider' => $serviceProviderName,
                                ]);
                            } while ($reason = $reason->getPrevious());
                        } else {
                            $this->logger->error('Error on get available prices: non exception reason {reasonType}', [
                                'reasonType' => \is_object($reason) ? \get_class($reason) : \gettype($reason),
                            ]);
                        }
                    }
                );
        }

        (new EachPromise($promises))->promise()->wait();

        if ($errorCount && $errorCount === \count($aggregated)) {
            throw new ServiceProviderResponseException('Can`t find available rates, all service provider responded with an error');
        }

        $response = new \SplObjectStorage();
        foreach ($prices as $serviceProviderName => $responses) {
            $response->attach(
                $spAliases[$serviceProviderName],
                \count($responses) > 1 ? array_merge(...$responses) : current($responses)
            );
        }

        return $response;
    }
}