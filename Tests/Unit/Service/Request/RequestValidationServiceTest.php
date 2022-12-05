<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Unit\Service\Request;

use Hyvor\BlogsBundle\Service\Request\RequestValidationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

class RequestValidationServiceTest extends TestCase
{
    private const HEADER_NAME = 'header';
    private const ALGORITHM = 'sha256';

    /**
     * @var RequestValidationService
     */
    private $requestValidationService;

    protected function setUp(): void
    {
         $this->requestValidationService = new RequestValidationService(self::ALGORITHM, self::HEADER_NAME);
    }

    public function testValidate(): void
    {
        $request = new Request([], [], [], [], [], [], 'content');
        $request->headers = new HeaderBag();
        $request->headers->set(self::HEADER_NAME, '230d8225fa8d7c42c0d16356ff175a660c11960fc035616023b8d22a8f36f03a');

        self::assertTrue($this->requestValidationService->validate($request, 'secret'));
    }

    public function testValidateInvalidSignature(): void
    {
        $request = new Request([], [], [], [], [], [], 'content');
        $request->headers = new HeaderBag();
        $request->headers->set(self::HEADER_NAME, 'invalid');

        self::assertFalse($this->requestValidationService->validate($request, 'secret'));
    }

    public function testValidateNoHeader(): void
    {
        $request = new Request([], [], [], [], [], [], 'content');
        $request->headers = new HeaderBag();

        self::assertFalse($this->requestValidationService->validate($request, 'secret'));
    }
}
