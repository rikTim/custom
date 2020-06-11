<?php


namespace Apl\HotelsDbBundle\Service\Location;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Location\Country;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Repository\Location\CountryRepository;
use Apl\HotelsDbBundle\Service\CacheAwareTrait;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\LocaleDetector\LocaleDetectorAwareTrait;
use Psr\Cache\CacheItemInterface;

class CountryService
{
    use EntityManagerAwareTrait,
        CacheAwareTrait,
        LocaleDetectorAwareTrait;


    /**
     * @var CacheItemInterface[]
     */
    private $cacheItems = [];

    /**
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     */
    public function getAllCountriesArray()
    {
        $locale = $this->localeDetector->getCurrentLocale();
        $countries = $this->getCountryFromCache($locale);
        if (!$countries) {
            $countries = $this->getCountryFromRepository($locale);
            if (null === $countries) {
                throw new RuntimeException(sprintf('Not exist currency from locate "%s"', $locale));
            }
            $this->pushCountyToCache($countries, $locale);
        }

        return $countries;
    }

    /**
     * @param Locale $locale
     * @return CacheItemInterface
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getCacheItem(Locale $locale): CacheItemInterface
    {
        $cacheKey = 'all_county_' . $locale->getLanguageCode();
        if (!isset($this->cacheItems[$cacheKey])) {
            try {
                $this->cacheItems[$cacheKey] = $this->cache->getItem($cacheKey);
            } catch (InvalidArgumentException $e) {
                throw new RuntimeException('Cache error: ' . $e->getMessage(), $e->getCode(), $e);
            }
        }
        return $this->cacheItems[$cacheKey];
    }

    /**
     * @param Locale $locale
     * @return array|null
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getCountryFromCache(Locale $locale): ? array
    {
        $cacheItem = $this->getCacheItem($locale);
        return $cacheItem->isHit()
            ? $cacheItem->get()
            : null;
    }

    /**
     * @return CountryRepository
     */
    private function getRepository(): CountryRepository
    {
        return $this->entityManager->getRepository(Country::class);
    }

    /**
     * @param Locale $locale
     * @return array
     */
    private function getCountryFromRepository(Locale $locale): array
    {
        $countries = $this->getRepository()->findAll();
        $array = [];
        /**
         * @var Country $country
         */
        foreach ($countries as $country) {
            $array[(string)$country->getTranslate('name', $locale)] = $country->getIsoCode();
        }
        return $array;
    }

    /**
     * @param array $countries
     * @param Locale $locale
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     */
    private function pushCountyToCache(array $countries, Locale $locale): void
    {
        $cacheItem = $this->getCacheItem($locale);
        $cacheItem->set($countries);
        $this->cache->save($cacheItem);
    }

}