<?php


namespace Apl\HotelsDbBundle\Service;


use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;


/**
 * Trait CacheAwareTrait
 * @package Apl\HotelsDbBundle\Service
 */
trait CacheAwareTrait
{
    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * @var CacheItemInterface[]
     */
    private $cacheItems;

    /**
     * @param AdapterInterface $cache
     * @required
     */
    public function setCache(AdapterInterface $cache): void
    {
        $this->cache = $cache;
        $this->cacheItems = [];
    }

    /**
     * @param string $key
     * @param callable $generator
     * @param null $ttl
     * @return mixed
     */
    public function getCachedValue(string $key, callable $generator, $ttl = null)
    {
        $item = $this->getCacheItem($key);
        if (!$item->isHit()) {
            $value = $generator();
            $this->setCachedValue($key, $value, $ttl);
            return $value;
        }

        return $item->get();
    }

    /**
     * @param string $key
     * @param $value
     * @param null $ttl
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function setCachedValue(string $key, $value, $ttl = null): bool
    {
        return $this->cache->save(
            $this->getCacheItem($key)
                ->set($value)
                ->expiresAfter($ttl)
        );
    }

    /**
     * @param string $key
     * @return CacheItemInterface
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getCacheItem(string $key): CacheItemInterface
    {
        return $this->cacheItems[$key] ?? $this->cacheItems[$key] = $this->cache->getItem($key);
    }
}