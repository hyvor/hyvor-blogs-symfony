<?php

namespace Hyvor\BlogBundle\Service\Cache;

use Hyvor\BlogBundle\DTO\DeliveryAPIResponseObject;

class CacheService
{
    public const LAST_TEMPLATE_CACHE_CLEARED_AT = 'LAST_TEMPLATE_CACHE_CLEARED_AT';

    public const LAST_ALL_CACHE_CLEARED_AT = 'LAST_ALL_CACHE_CLEARED_AT';

    /**
     * @var CacheRegistry
     */
    private $cacheRegistry;

    /**
     * @var CacheKeyProvider
     */
    private $cacheKeyProvider;

    public function __construct(CacheRegistry $cacheRegistry, CacheKeyProvider $cacheKeyProvider)
    {
        $this->cacheRegistry = $cacheRegistry;
        $this->cacheKeyProvider = $cacheKeyProvider;
    }

    public function set(string $subdomain, string $path, DeliveryAPIResponseObject $response) : void
    {
        $this->saveToCache($subdomain, $path, json_encode($response));
    }

    public function get(string $subdomain, string $path): ?DeliveryAPIResponseObject
    {
        $cached = $this->getFromCache($subdomain, $path);
        if (!$cached) {
            return null;
        }

        $response = DeliveryAPIResponseObject::createFromJson($cached);
        if (!$response) {
            return null;
        }

        $at = $response->at;
        $lastCacheAllClearedAt = $this->getFromCache($subdomain,self::LAST_ALL_CACHE_CLEARED_AT) ?? 0;
        if ($at < $lastCacheAllClearedAt) {
            return null;
        }

        if (
            $response->type === DeliveryAPIResponseObject::TYPE_FILE &&
            $response->file_type === DeliveryAPIResponseObject::FILE_TYPE_TEMPLATE
        ) {
            $templateCacheClearedAt = $this->getFromCache($subdomain, self::LAST_TEMPLATE_CACHE_CLEARED_AT) ?? 0;
            if ($at < $templateCacheClearedAt) {
                return null;
            }
        }

        return $response;
    }

    public function clearSingleCache(string $subdomain, string $path) : void
    {
        $cachePool = $this->cacheRegistry->getCachePool($subdomain);
        $cachePool->deleteItem($this->cacheKeyProvider->getCacheKey($subdomain, $path));
    }

    public function clearTemplateCache(string $subdomain) : void
    {
        $this->saveToCache(
            $subdomain,
            self::LAST_TEMPLATE_CACHE_CLEARED_AT,
            (new \DateTimeImmutable())->getTimestamp()
        );
    }

    public function clearAllCache(string $subdomain) : void
    {
        $this->saveToCache(
            $subdomain,
            self::LAST_ALL_CACHE_CLEARED_AT,
            (new \DateTimeImmutable())->getTimestamp()
        );
    }

    private function getFromCache(string $subdomain, string $key): ?string
    {
        $key = $this->cacheKeyProvider->getCacheKey($subdomain, $key);
        $cachePool = $this->cacheRegistry->getCachePool($subdomain);
        $cached = $cachePool->getItem($key);

        return $cached->isHit() ? $cached->get() : null;
    }

    private function saveToCache(string $subdomain, string $key, string $value): void
    {
        $key = $this->cacheKeyProvider->getCacheKey($subdomain, $key);
        $cachePool = $this->cacheRegistry->getCachePool($subdomain);
        $cacheItem = $cachePool->getItem($key);
        $cacheItem->set($value);
        $cachePool->save($cacheItem);
    }
}
