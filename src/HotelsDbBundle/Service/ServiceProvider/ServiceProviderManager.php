<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider;


use Apl\HotelsDbBundle\Exception\InvalidArgumentException;

/**
 * Class ServiceProviderManager
 *
 * @package Apl\HotelsDbBundle\Service\ServiceProvider
 */
class ServiceProviderManager implements \IteratorAggregate
{
    public const SYSTEM_SERVICE_PROVIDER = 'system_provider';

    /**
     * @var ServiceProviderInterface[]
     */
    private $collection;

    public function __construct()
    {
        $this->collection = [];
    }

    /**
     * @param ServiceProviderInterface $providerBridge
     */
    public function addServiceProvider(ServiceProviderInterface $providerBridge): void
    {
        $this->collection[(string)$providerBridge->getServiceProviderAlias()] = $providerBridge;
    }

    /**
     * @param string $serviceProviderName
     * @return ServiceProviderInterface
     */
    public function getServiceProvider(string $serviceProviderName): ServiceProviderInterface
    {
        if (isset($this->collection[$serviceProviderName])) {
            return $this->collection[$serviceProviderName];
        }

        throw new InvalidArgumentException(sprintf('Undefined service provider %s', $serviceProviderName));
    }

    /**
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }
}