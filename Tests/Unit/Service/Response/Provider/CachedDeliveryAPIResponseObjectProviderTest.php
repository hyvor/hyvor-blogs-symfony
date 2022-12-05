<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Unit\Service\Response\Provider;

use Hyvor\BlogsBundle\DTO\DeliveryAPIResponseObject;
use Hyvor\BlogsBundle\Service\Cache\CacheService;
use Hyvor\BlogsBundle\Service\Response\Provider\CachedDeliveryAPIResponseObjectProvider;
use Hyvor\BlogsBundle\Service\Response\Provider\DeliveryAPIResponseObjectProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class CachedDeliveryAPIResponseObjectProviderTest extends TestCase
{
    /**
     * @var DeliveryAPIResponseObjectProviderInterface|ObjectProphecy
     */
    private $deliveryAPIResponseObjectProviderProphecy;

    /**
     * @var CacheService|ObjectProphecy
     */
    private $cacheServiceProphecy;

    /**
     * @var CachedDeliveryAPIResponseObjectProvider
     */
    private $cachedDeliveryAPIResponseObjectProvider;

    protected function setUp(): void
    {
        $this->deliveryAPIResponseObjectProviderProphecy = $this->prophesize(
            DeliveryAPIResponseObjectProviderInterface::class
        );
        $this->cacheServiceProphecy = $this->prophesize(CacheService::class);
        $this->cachedDeliveryAPIResponseObjectProvider = new CachedDeliveryAPIResponseObjectProvider(
            $this->deliveryAPIResponseObjectProviderProphecy->reveal(),
            $this->cacheServiceProphecy->reveal()
        );
    }

    public function testGetResponseObjectFromCache(): void
    {
        $deliveryAPIResponseObject = new DeliveryAPIResponseObject();
        $this->cacheServiceProphecy->get('subdomain', 'path')
            ->willReturn($deliveryAPIResponseObject)
            ->shouldBeCalled();
        $this->deliveryAPIResponseObjectProviderProphecy->getResponseObject(Argument::cetera())
            ->shouldNotBeCalled();
        $this->assertEquals(
            $deliveryAPIResponseObject,
            $this->cachedDeliveryAPIResponseObjectProvider->getResponseObject('subdomain', 'path')
        );
    }

    public function testGetResponseObjectNoCached(): void
    {
        $deliveryAPIResponseObject = new DeliveryAPIResponseObject();
        $this->cacheServiceProphecy->get('subdomain', 'path')
            ->willReturn(null)
            ->shouldBeCalled();
        $this->deliveryAPIResponseObjectProviderProphecy->getResponseObject('subdomain', 'path')
            ->willReturn($deliveryAPIResponseObject)
            ->shouldBeCalled();
        $deliveryAPIResponseObject->cache = false;
        $this->cacheServiceProphecy->set(Argument::cetera())
            ->shouldNotBeCalled();
        $this->assertEquals(
            $deliveryAPIResponseObject,
            $this->cachedDeliveryAPIResponseObjectProvider->getResponseObject('subdomain', 'path')
        );
    }

    public function testGetResponseObject(): void
    {
        $deliveryAPIResponseObject = new DeliveryAPIResponseObject();
        $this->cacheServiceProphecy->get('subdomain', 'path')
            ->willReturn(null)
            ->shouldBeCalled();
        $this->deliveryAPIResponseObjectProviderProphecy->getResponseObject('subdomain', 'path')
            ->willReturn($deliveryAPIResponseObject)
            ->shouldBeCalled();
        $deliveryAPIResponseObject->cache = true;
        $this->cacheServiceProphecy->set('subdomain', 'path', $deliveryAPIResponseObject)
            ->shouldBeCalled();
        $this->assertEquals(
            $deliveryAPIResponseObject,
            $this->cachedDeliveryAPIResponseObjectProvider->getResponseObject('subdomain', 'path')
        );
    }
}
