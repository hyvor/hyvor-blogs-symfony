<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Unit\Routing;

use Hyvor\BlogsBundle\Configuration\Configuration;
use Hyvor\BlogsBundle\Controller\WebhookController;
use Hyvor\BlogsBundle\Exception\UnknownSubdomainException;
use Hyvor\BlogsBundle\Routing\WebhookRequestMatcher;
use Hyvor\BlogsBundle\Service\Configuration\Registry\ConfigurationRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class WebhookRequestMatcherTest extends TestCase
{
    /**
     * @var ConfigurationRegistry|ObjectProphecy
     */
    private $configurationRegistryProphecy;

    /**
     * @var WebhookRequestMatcher
     */
    private $webhookRequestMatcher;

    protected function setUp(): void
    {
        $this->configurationRegistryProphecy = $this->prophesize(ConfigurationRegistry::class);
        $this->webhookRequestMatcher = new WebhookRequestMatcher(
            $this->configurationRegistryProphecy->reveal(),
            '/hyvorblogs/webhook',
            'POST'
        );
    }

    public function testMatchRequestWrongPath(): void
    {
        $request = Request::create('https://blogs.hyvor.com/hyvorblogs/webhook-wrong-path', 'POST');
        $this->expectException(ResourceNotFoundException::class);
        $this->webhookRequestMatcher->matchRequest($request);
    }

    public function testMatchRequestWrongMethod(): void
    {
        $request = Request::create('https://blogs.hyvor.com/hyvorblogs/webhook', 'GET');
        $this->expectException(ResourceNotFoundException::class);
        $this->webhookRequestMatcher->matchRequest($request);
    }

    public function testMatchRequestWrongSubdomain(): void
    {
        $request = Request::create('https://blogs.hyvor.com/hyvorblogs/webhook', 'POST');
        $this->configurationRegistryProphecy->getConfiguration('blogs.hyvor.com')
            ->willThrow(UnknownSubdomainException::class)
            ->shouldBeCalled();
        $this->expectException(ResourceNotFoundException::class);
        $this->webhookRequestMatcher->matchRequest($request);
    }

    public function testMatchRequest(): void
    {
        $configuration = new Configuration(
            'foo',
            'bar',
            'blogs.hyvor.com',
            '/hyvorblogs/webhook'
        );
        $request = Request::create('https://blogs.hyvor.com/hyvorblogs/webhook', 'POST');
        $this->configurationRegistryProphecy->getConfiguration('blogs.hyvor.com')
            ->willReturn($configuration)
            ->shouldBeCalled();

        self::assertSame(
            [
                '_controller' => WebhookController::class,
            ],
            $this->webhookRequestMatcher->matchRequest($request)
        );
    }
}
