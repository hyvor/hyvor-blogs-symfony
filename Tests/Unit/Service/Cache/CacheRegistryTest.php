<?php

namespace Hyvor\BlogBundle\Tests\Unit\Service\Cache;

use Hyvor\BlogBundle\Service\Cache\CacheRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

class CacheRegistryTest extends TestCase
{
    private $cacheRegistry;

    protected function setUp(): void
    {
        $this->cacheRegistry = new CacheRegistry();
    }

    public function testGetWrongCachePool()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache pool for subdomain "foo" not found');
        $this->cacheRegistry->getCachePool('foo');
    }

    public function testAddCachePool()
    {
        $cachePool = $this->prophesize(CacheItemPoolInterface::class);
        $this->cacheRegistry->addCachePool('foo', $cachePool->reveal());
        $this->assertEquals(
            $cachePool->reveal(),
            $this->cacheRegistry->getCachePool('foo')
        );
    }
}
