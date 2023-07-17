<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Controller;

use Hyvor\BlogsBundle\Service\Response\Factory\ResponseFactory;
use Hyvor\BlogsBundle\Service\Response\Provider\DeliveryAPIResponseObjectProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController
{
    private const ERROR_MESSAGE = 'Error';

    /**
     * @var DeliveryAPIResponseObjectProviderInterface
     */
    private $deliveryAPIResponseObjectProvider;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    public function __construct(
        DeliveryAPIResponseObjectProviderInterface $deliveryAPIResponseObjectProvider,
        ResponseFactory $responseFactory
    ) {
        $this->deliveryAPIResponseObjectProvider = $deliveryAPIResponseObjectProvider;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(Request $request, string $subdomain, string $path): Response
    {
        try {
            return $this->responseFactory->create(
                $this->deliveryAPIResponseObjectProvider->getResponseObject(
                    $subdomain,
                    $path
                )
            );
        } catch (\Exception $e) {
            return new Response(self::ERROR_MESSAGE);
        }
    }
}
