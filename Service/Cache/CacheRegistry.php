<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Service\Cache;

use Psr\Cache\CacheItemPoolInterface;

class CacheRegistry
{
    /**
     * @var array<string, CacheItemPoolInterface>
     */
    private $cachePools = [];

    public function getCachePool(string $subdomain) : CacheItemPoolInterface
    {
        if (!isset($this->cachePools[$subdomain])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cache pool for subdomain "%s" not found',
                    $subdomain
                )
            );
        }

        return $this->cachePools[$subdomain];
    }

    public function addCachePool(string $subdomain, CacheItemPoolInterface $cacheItemPool): self
    {
        $this->cachePools[$subdomain] = $cacheItemPool;

        return $this;
    }
}
