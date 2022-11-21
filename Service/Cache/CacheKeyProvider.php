<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\Service\Cache;

class CacheKeyProvider
{
    /**
     * @var string
     */
    private $cacheKeyTemplate;

    public function __construct(string $cacheKeyTemplate)
    {
        $this->cacheKeyTemplate = $cacheKeyTemplate;
    }

    public function getCacheKey(string $subdomain, string $key) : string
    {
        return sprintf($this->cacheKeyTemplate, $subdomain, urlencode($key));
    }
}
