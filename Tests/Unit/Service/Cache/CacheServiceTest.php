<?php

namespace Hyvor\BlogBundle\Tests\Unit\Service\Cache;

use Hyvor\BlogBundle\DTO\DeliveryAPIResponseObject;
use Hyvor\BlogBundle\Service\Cache\CacheKeyProvider;
use Hyvor\BlogBundle\Service\Cache\CacheRegistry;
use Hyvor\BlogBundle\Service\Cache\CacheService;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheServiceTest extends TestCase
{
    /**
     * @var CacheKeyProvider|ObjectProphecy
     */
    private $cacheKeyProviderMock;

    /**
     * @var CacheRegistry|ObjectProphecy
     */
    private $cacheRegistryMock;

    /**
     * @var CacheService
     */
    private $cacheService;

    protected function setUp(): void
    {
        $this->cacheKeyProviderMock = $this->prophesize(CacheKeyProvider::class);
        $this->cacheRegistryMock = $this->prophesize(CacheRegistry::class);
        $this->cacheService = new CacheService(
            $this->cacheRegistryMock->reveal(),
            $this->cacheKeyProviderMock->reveal()
        );
    }

    public function testSet(): void
    {
        $deliveryAPIResponseObject = new DeliveryAPIResponseObject();
        $this->expectSave('subdomain', 'key', '{"type":null,"at":null,"file_type":null,"content":null,"mime_type":null,"to":null,"cache":null,"status":null}');
        $this->cacheService->set('subdomain', 'key', $deliveryAPIResponseObject);
    }

    public function testClearSingleCache(): void
    {
        $this->cacheKeyProviderMock->getCacheKey('subdomain', 'key')->willReturn('cache_key');
        $cachePoolMock = $this->prophesize(CacheItemPoolInterface::class);
        $this->cacheRegistryMock->getCachePool('subdomain')->willReturn($cachePoolMock->reveal());
        $cachePoolMock->deleteItem('cache_key')
            ->shouldBeCalled();
        $this->cacheService->clearSingleCache('subdomain', 'key');
    }

    public function testClearTemplateCache(): void
    {
        $this->expectSave('subdomain', CacheService::LAST_TEMPLATE_CACHE_CLEARED_AT, Argument::cetera());
        $this->cacheService->clearTemplateCache('subdomain');
    }

    public function testClearAllCache(): void
    {
        $this->expectSave('subdomain', CacheService::LAST_ALL_CACHE_CLEARED_AT, Argument::cetera());
        $this->cacheService->clearAllCache('subdomain');
    }

    public function testGetNotCached(): void
    {
        $this->expectGet('subdomain', 'key', false);
        $this->assertNull($this->cacheService->get('subdomain', 'key'));
    }

    public function testGetWrongResponse(): void
    {
        $this->expectGet('subdomain', 'key', true, 'wrong_response');
        $this->assertNull($this->cacheService->get('subdomain', 'key'));
    }

    private function expectSave(string $subdomain, string $key, $data): void
    {
        $this->cacheKeyProviderMock->getCacheKey($subdomain, $key)->willReturn('cache_key');
        $cachePoolMock = $this->prophesize(CacheItemPoolInterface::class);
        $this->cacheRegistryMock->getCachePool($subdomain)->willReturn($cachePoolMock->reveal());
        $cacheItemMock = $this->prophesize(CacheItemInterface::class);
        $cachePoolMock->getItem('cache_key')->willReturn($cacheItemMock->reveal());
        $cacheItemMock->set($data)
            ->willReturn($cacheItemMock->reveal())
            ->shouldBeCalled();
        $cachePoolMock->save($cacheItemMock->reveal())
            ->shouldBeCalled();
    }

    private function expectGet(string $subdomain, string $key, bool $success = false, ?string $data = null): void
    {
        $this->cacheKeyProviderMock->getCacheKey($subdomain, $key)->willReturn('cache_key');
        $cachePoolMock = $this->prophesize(CacheItemPoolInterface::class);
        $this->cacheRegistryMock->getCachePool($subdomain)->willReturn($cachePoolMock->reveal());
        $cacheItemMock = $this->prophesize(CacheItemInterface::class);
        $cachePoolMock->getItem('cache_key')->willReturn($cacheItemMock->reveal());
        $cacheItemMock->isHit()
            ->willReturn($success)
            ->shouldBeCalled();
        if ($success) {
            $cacheItemMock->get()
                ->willReturn($data)
                ->shouldBeCalled();
        }
    }
}
