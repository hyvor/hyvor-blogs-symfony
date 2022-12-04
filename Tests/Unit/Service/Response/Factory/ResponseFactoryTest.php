<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Unit\Service\Response\Factory;

use Hyvor\BlogsBundle\DTO\DeliveryAPIResponseObject;
use Hyvor\BlogsBundle\Service\Response\Factory\ResponseFactory;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    protected function setUp(): void
    {
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateRedirect(): void
    {
        $deliverAPIResponseObject = new DeliveryAPIResponseObject();
        $deliverAPIResponseObject->type = DeliveryAPIResponseObject::TYPE_REDIRECT;
        $deliverAPIResponseObject->to = 'https://example.com';
        $deliverAPIResponseObject->status = 302;
        $result = $this->responseFactory->create($deliverAPIResponseObject);
        self::assertSame($result->getStatusCode(), 302);
        self::assertSame($result->getTargetUrl(), 'https://example.com');
    }

    public function testCreate(): void
    {
        $deliverAPIResponseObject = new DeliveryAPIResponseObject();
        $deliverAPIResponseObject->type = DeliveryAPIResponseObject::TYPE_FILE;
        $deliverAPIResponseObject->mime_type = 'foo/bar';
        $deliverAPIResponseObject->status = 200;
        $deliverAPIResponseObject->content = base64_encode('content');
        $result = $this->responseFactory->create($deliverAPIResponseObject);
        self::assertSame($result->getStatusCode(), 200);
        self::assertSame($result->getContent(), 'content');
        self::assertSame($result->headers->get('Content-Type'), 'foo/bar');
    }
}
