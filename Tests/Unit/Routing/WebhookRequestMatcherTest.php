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
     * @var WebhookRequestMatcher
     */
    private $webhookRequestMatcher;

    protected function setUp(): void
    {
        $this->webhookRequestMatcher = new WebhookRequestMatcher(
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

    public function testMatchRequest(): void
    {
        $request = Request::create('https://blogs.hyvor.com/hyvorblogs/webhook', 'POST');

        self::assertSame(
            [
                '_controller' => WebhookController::class,
            ],
            $this->webhookRequestMatcher->matchRequest($request)
        );
    }
}
