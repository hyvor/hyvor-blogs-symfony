<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Unit\Service\Response\Provider;

use Hyvor\BlogsBundle\Configuration\Configuration;
use Hyvor\BlogsBundle\DTO\DeliveryAPIResponseObject;
use Hyvor\BlogsBundle\Service\Configuration\Registry\ConfigurationRegistry;
use Hyvor\BlogsBundle\Service\Response\Provider\DeliveryAPIResponseObjectProvider;
use Hyvor\BlogsBundle\Service\Response\Provider\RequestUrlProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DeliveryAPIResponseObjectProviderTest extends TestCase
{
    /**
     * @var HttpClientInterface|ObjectProphecy
     */
    private $httpClientProphecy;

    /**
     * @var RequestUrlProvider|ObjectProphecy
     */
    private $requestUrlProviderProphecy;

    /**
     * @var ConfigurationRegistry|ObjectProphecy
     */
    private $configurationRegistryProphecy;

    /**
     * @var DeliveryAPIResponseObjectProvider
     */
    private $deliveryAPIResponseObjectProvider;

    protected function setUp(): void
    {
        $this->httpClientProphecy = $this->prophesize(HttpClientInterface::class);
        $this->requestUrlProviderProphecy = $this->prophesize(RequestUrlProvider::class);
        $this->configurationRegistryProphecy = $this->prophesize(ConfigurationRegistry::class);
        $this->deliveryAPIResponseObjectProvider = new DeliveryAPIResponseObjectProvider(
            $this->httpClientProphecy->reveal(),
            $this->requestUrlProviderProphecy->reveal(),
            $this->configurationRegistryProphecy->reveal()
        );
    }

    public function testGetResponseObject(): void
    {
        $blogConfiguration = new Configuration('foo', 'bar', 'blogs.hyvor.com', '/qux');
        $this->configurationRegistryProphecy->getConfiguration('blogs.hyvor.com')
            ->willReturn($blogConfiguration)
            ->shouldBeCalled();
        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getContent()
            ->willReturn(
                '{"type": "redirect", "at":12345, "cache": false, "status": 302, "to": "https://blogs.hyvor.com"}'
            )
            ->shouldBeCalled();
        $this->requestUrlProviderProphecy->getRequestUrl('blogs.hyvor.com')
            ->willReturn('https://blogs.hyvor.com')
            ->shouldBeCalled();
        $this->httpClientProphecy->request(
            'GET',
            'https://blogs.hyvor.com',
            [
                'query' => [
                    'path' => '/not-a-real-path',
                    'api_key' => 'foo'
                ]
            ]
        )
            ->willReturn($responseProphecy->reveal())
            ->shouldBeCalled();
        self::assertInstanceOf(
            DeliveryAPIResponseObject::class,
            $this->deliveryAPIResponseObjectProvider->getResponseObject('blogs.hyvor.com', '/not-a-real-path')
        );
    }
}
