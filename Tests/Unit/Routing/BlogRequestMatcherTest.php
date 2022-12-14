<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Unit\Routing;

use Hyvor\BlogsBundle\Configuration\Configuration;
use Hyvor\BlogsBundle\Controller\BlogController;
use Hyvor\BlogsBundle\Exception\UnknownSubdomainException;
use Hyvor\BlogsBundle\Routing\BlogRequestMatcher;
use Hyvor\BlogsBundle\Service\Configuration\Registry\ConfigurationRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class BlogRequestMatcherTest extends TestCase
{
    /**
     * @var ConfigurationRegistry|ObjectProphecy
     */
    private $configurationRegistryProphecy;

    /**
     * @var BlogRequestMatcher
     */
    private $blogRequestMatcher;

    protected function setUp(): void
    {
        $this->configurationRegistryProphecy = $this->prophesize(ConfigurationRegistry::class);
        $this->blogRequestMatcher = new BlogRequestMatcher($this->configurationRegistryProphecy->reveal(), 'GET');
    }

    public function testMatchRequestWrongMethod(): void
    {
        $request = Request::create('https://blogs.hyvor.com/blog', 'POST');
        $this->expectException(ResourceNotFoundException::class);
        $this->blogRequestMatcher->matchRequest($request);
    }

    public function testMatchRequestWrongSubdomain(): void
    {
        $configuration = new Configuration('foo', 'bar', 'blogs-hyvor-com', '/foo');
        $request = Request::create('https://blogs.hyvor.com/blog', 'GET');
        $this->configurationRegistryProphecy->getConfigurations()
            ->willReturn([$configuration])
            ->shouldBeCalled();
        $this->expectException(ResourceNotFoundException::class);
        $this->blogRequestMatcher->matchRequest($request);
    }

    public function testMatchRequestRootPath(): void
    {
        $request = Request::create('https://blogs.hyvor.com/blog', 'GET');
        $configuration = new Configuration('foo', 'bar', 'blogs-hyvor-com', '/blog');
        $this->configurationRegistryProphecy->getConfigurations()
            ->willReturn([$configuration])
            ->shouldBeCalled();

        self::assertSame(
            [
                '_controller' => BlogController::class,
                'subdomain' => 'blogs-hyvor-com',
                'path' => '/',
            ],
            $this->blogRequestMatcher->matchRequest($request)
        );
    }

    public function testMatchRequest(): void
    {
        $request = Request::create('https://blogs.hyvor.com/blog/some/path', 'GET');
        $configuration = new Configuration('foo', 'bar', 'blogs-hyvor-com', '/blog');
        $this->configurationRegistryProphecy->getConfigurations()
            ->willReturn([$configuration])
            ->shouldBeCalled();

        self::assertSame(
            [
                '_controller' => BlogController::class,
                'subdomain' => 'blogs-hyvor-com',
                'path' => '/some/path',
            ],
            $this->blogRequestMatcher->matchRequest($request)
        );
    }
}
