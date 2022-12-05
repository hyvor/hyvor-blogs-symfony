<?php

namespace Hyvor\BlogsBundle\Tests\Unit\Service\Cache;

use Hyvor\BlogsBundle\Service\Cache\CacheKeyProvider;
use PHPUnit\Framework\TestCase;

class CacheKeyProviderTest extends TestCase
{
    private $cacheKeyProvider;

    protected function setUp(): void
    {
        $this->cacheKeyProvider = new CacheKeyProvider('data_%s_%s');
    }

    public function testGetCacheKey()
    {
        $this->assertEquals(
            'data_foo_bar',
            $this->cacheKeyProvider->getCacheKey('foo', 'bar')
        );
    }
}
