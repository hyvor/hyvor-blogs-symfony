<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Functional;

use Hyvor\BlogsBundle\Controller\WebhookController;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class WebhookControllerTest extends WebTestCase
{
    /**
     * @var CacheItemPoolInterface
     */
    protected $cachePool;

    /**
     * @var KernelBrowser
     */
    protected $client;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel('test', false);
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->cachePool = static::getContainer()->get('cache.app');
        $this->cachePool->clear();
    }

    public function testWebhookInvalidSignature()
    {
        $response = $this->sendRequest('localhost', 'cache.all', [], 'invalid-signature');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(WebhookController::RESPONSE_MESSAGE_INVALID_WEBHOOK, $response->getContent());
    }

    public function testWebhookInvalidSubdomain()
    {
        $response = $this->sendRequest('invalid.subdomain', 'cache.all');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(WebhookController::RESPONSE_MESSAGE_INVALID_SUBDOMAIN, $response->getContent());
    }

    public function testWebhookEventCacheAll()
    {
        $itemName = 'hyvor_blogs__localhost__LAST_ALL_CACHE_CLEARED_AT';
        $this->assertFalse($this->cachePool->getItem($itemName)->isHit());
        $response = $this->sendRequest('localhost', 'cache.all');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(WebhookController::RESPONSE_MESSAGE, $response->getContent());
        $this->assertTrue($this->cachePool->getItem($itemName)->isHit());
    }

    public function testWebhookEventCacheTemplate()
    {
        $itemName = 'hyvor_blogs__localhost__LAST_TEMPLATE_CACHE_CLEARED_AT';
        $this->assertFalse($this->cachePool->getItem($itemName)->isHit());
        $response = $this->sendRequest('localhost', 'cache.templates');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(WebhookController::RESPONSE_MESSAGE, $response->getContent());
        $this->assertTrue($this->cachePool->getItem($itemName)->isHit());
    }

    public function testWebhookEventCacheSingle()
    {
        $itemName = 'hyvor_blogs__localhost__%2Fpath';
        $item = $this->cachePool->getItem($itemName);
        $item->set(true);
        $this->cachePool->save($item);
        // Cache item should be hit after setting it
        $this->assertTrue($this->cachePool->getItem($itemName)->isHit());
        $response = $this->sendRequest('localhost', 'cache.single', ['path' => '/path']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(WebhookController::RESPONSE_MESSAGE, $response->getContent());
        // Cache item should be miss after clearing it
        $this->assertFalse($this->cachePool->getItem($itemName)->isHit());
    }

    private function sendRequest(string $subdomain, string $event, array $data = [], string $signature = null): Response
    {
        $payload = [
            'subdomain' => $subdomain,
            'timestamp' => (new \DateTimeImmutable())->getTimestamp(),
            'event' => $event,
            'data' => $data
        ];

        $payloadAsString = json_encode($payload);
        if ($signature === null) {
            $signature = hash_hmac('sha256', $payloadAsString, 'your-webhook-secret');
        }

        $this->client->request(
            'POST',
            '/hyvorblogs/webhook',
            [],
            [],
            [
                'HTTP_X_SIGNATURE' => $signature
            ],
            $payloadAsString
        );

        return $this->client->getResponse();
    }
}
