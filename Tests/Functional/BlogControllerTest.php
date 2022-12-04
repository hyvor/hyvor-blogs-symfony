<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Functional;

use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BlogControllerTest extends WebTestCase
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var HttpClientInterface|MockObject
     */
    private $httpClientMock;

    protected static function createKernel(array $options = [])
    {
        return new TestKernel('test', false);
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cachePool = static::getContainer()->get('cache.app');
        $this->cachePool->clear();
        $this->httpClientMock = $this->prophesize(HttpClientInterface::class);
        static::getContainer()->set('hyvor_blog.service.http_client', $this->httpClientMock->reveal());
    }

    public function testRenderBlogFromAPIData()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'type' => 'file',
                        'at' => (new \DateTimeImmutable())->getTimestamp(),
                        'cache' => true,
                        'status' => 201,
                        'file_type' => 'template',
                        'content' => base64_encode('Test Index Page'),
                        'mime_type' => 'text/test'
                    ]
                )
            );
        $this->httpClientMock
            ->request(
                'GET',
                'https://blogs.hyvor.com/api/delivery/v0/localhost',
                ['query' => ['path' => '/', 'api_key' => 'your-delivery-api-key']]
            )
            ->willReturn($responseMock)
            ->shouldBeCalledOnce();
        $this->assertFalse($this->cachePool->getItem('hyvor_blogs__localhost__%2F')->isHit());
        $this->client->request('GET', '/blog');
        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Test Index Page', $response->getContent());
        $this->assertTrue($this->cachePool->getItem('hyvor_blogs__localhost__%2F')->isHit());
    }

    public function testRenderBlogWithPathFromAPIData()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'type' => 'file',
                        'at' => (new \DateTimeImmutable())->getTimestamp(),
                        'cache' => true,
                        'status' => 201,
                        'file_type' => 'template',
                        'content' => base64_encode('Test Index Page'),
                        'mime_type' => 'text/test'
                    ]
                )
            );
        $this->httpClientMock
            ->request(
                'GET',
                'https://blogs.hyvor.com/api/delivery/v0/localhost',
                ['query' => ['path' => '/path', 'api_key' => 'your-delivery-api-key']]
            )
            ->willReturn($responseMock)
            ->shouldBeCalledOnce();
        $this->assertFalse($this->cachePool->getItem('hyvor_blogs__localhost__%2Fpath')->isHit());
        $this->client->request('GET', '/blog/path');
        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Test Index Page', $response->getContent());
        $this->assertTrue($this->cachePool->getItem('hyvor_blogs__localhost__%2Fpath')->isHit());
    }

    public function testRedirectBlogFromAPIData()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'type' => 'redirect',
                        'at' => (new \DateTimeImmutable())->getTimestamp(),
                        'cache' => true,
                        'status' => 301,
                        'to' => 'https://supun.io'
                    ]
                )
            );
        $this->httpClientMock
            ->request(
                'GET',
                'https://blogs.hyvor.com/api/delivery/v0/localhost',
                ['query' => ['path' => '/path', 'api_key' => 'your-delivery-api-key']]
            )
            ->willReturn($responseMock)
            ->shouldBeCalledOnce();
        $this->assertFalse($this->cachePool->getItem('hyvor_blogs__localhost__%2Fpath')->isHit());
        $this->client->request('GET', '/blog/path');
        /** @var RedirectResponse $response */
        $response = $this->client->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('https://supun.io', $response->getTargetUrl());
        $this->assertTrue($this->cachePool->getItem('hyvor_blogs__localhost__%2Fpath')->isHit());
    }

    public function testRenderBlogFromCache()
    {
        $item = $this->cachePool
            ->getItem('hyvor_blogs__localhost__%2F')
            ->set(
                json_encode(
                    [
                        'type' => 'file',
                        'at' => (new \DateTimeImmutable())->getTimestamp(),
                        'cache' => true,
                        'status' => 201,
                        'file_type' => 'template',
                        'content' => base64_encode('Test Index Page'),
                        'mime_type' => 'text/test'
                    ]
                )
            );
        $this->cachePool->save($item);
        $this->httpClientMock
            ->request(Argument::cetera())
            ->shouldNotBeCalled();
        $this->assertTrue($this->cachePool->getItem('hyvor_blogs__localhost__%2F')->isHit());
        $this->client->request('GET', '/blog');
        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Test Index Page', $response->getContent());
    }

    public function testNotSaveDataInCache()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'type' => 'file',
                        'at' => (new \DateTimeImmutable())->getTimestamp(),
                        'cache' => false,
                        'status' => 201,
                        'file_type' => 'template',
                        'content' => base64_encode('Test Index Page'),
                        'mime_type' => 'text/test'
                    ]
                )
            );
        $this->httpClientMock
            ->request(
                'GET',
                'https://blogs.hyvor.com/api/delivery/v0/localhost',
                ['query' => ['path' => '/path', 'api_key' => 'your-delivery-api-key']]
            )
            ->willReturn($responseMock)
            ->shouldBeCalledOnce();
        $this->assertFalse($this->cachePool->getItem('hyvor_blogs__localhost__%2Fpath')->isHit());
        $this->client->request('GET', '/blog/path');
        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Test Index Page', $response->getContent());
        $this->assertFalse($this->cachePool->getItem('hyvor_blogs__localhost__%2Fpath')->isHit());
    }
}
