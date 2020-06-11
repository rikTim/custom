<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider;


trait ServiceProviderManagerAwareTrait
{
    /**
     * @var ServiceProviderManager
     */
    protected $serviceProviderManager;

    /**
     * @param ServiceProviderManager $serviceProviderManager
     * @required
     */
    public function setServiceProviderManager(ServiceProviderManager $serviceProviderManager): void
    {
        $this->serviceProviderManager = $serviceProviderManager;
    }
}