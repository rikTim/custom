<?php


namespace Apl\HotelsDbBundle\Service\Location;


trait CountryServiceAwareTrait
{

    /**
     * @var CountryService
     */
    private $countryService;

    /**
     * @param CountryService $countryService
     * @required
     */
    public function setCountryService(CountryService $countryService): void
    {
        $this->countryService = $countryService;
    }

}