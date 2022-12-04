<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Unit\Service\Response\Provider;

use Hyvor\BlogsBundle\Service\Response\Provider\RequestUrlProvider;
use PHPUnit\Framework\TestCase;

class RequestUrlProviderTest extends TestCase
{
    /**
     * @var RequestUrlProvider
     */
    private $requestUriProvider;

    protected function setUp(): void
    {
        $this->requestUriProvider = new RequestUrlProvider('https://%s.example.com');
    }

    public function testGetRequestUrl(): void
    {
        self::assertSame('https://foo.example.com', $this->requestUriProvider->getRequestUrl('foo'));
    }
}
