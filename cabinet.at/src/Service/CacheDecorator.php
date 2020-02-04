<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

class CacheDecorator
{
    /** @var AdapterInterface */
    private $cache;

    /**
     * CacheDecorator constructor.
     * @param AdapterInterface $cache
     */
    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @return mixed|null
     * @throws InvalidArgumentException
     */
    public function getCachedData(string $key)
    {
        $cachedItem = $this->getCacheItem($key);
        if ($cachedItem->isHit() === false) {
            return null;
        }

        return $cachedItem->get();
    }

    /**
     * @param string $key
     * @param mixed $data
     * @throws InvalidArgumentException
     */
    public function saveDataToCache(string $key, $data): void
    {
        $cachedItem = $this->getCacheItem($key);
        $cachedItem->set($data);
        $this->cache->save($cachedItem);
    }

    /**
     * @param string $key
     * @throws InvalidArgumentException
     */
    public function clearDataByKey(string $key): void
    {
        $cachedItem = $this->getCacheItem($key);

        if ($cachedItem->isHit() !== false) {
            $this->cache->deleteItem(\base64_encode($key));
        }
    }

    /**
     * @param string $key
     * @return CacheItem
     * @throws InvalidArgumentException
     */
    private function getCacheItem(string $key): ?CacheItem
    {
        $key = \base64_encode($key);
        return $this->cache->getItem($key);
    }
}

