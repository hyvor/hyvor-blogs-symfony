<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\Service\Response\Provider;

use Hyvor\BlogBundle\DTO\DeliveryAPIResponseObject;
use Hyvor\BlogBundle\Service\Cache\CacheService;

class CachedDeliveryAPIResponseObjectProvider implements DeliveryAPIResponseObjectProviderInterface
{
    /**
     * @var DeliveryAPIResponseObjectProviderInterface
     */
    private $deliveryAPIResponseObjectProvider;

    /**
     * @var CacheService
     */
    private $cacheService;

    public function __construct(
        DeliveryAPIResponseObjectProviderInterface $deliveryAPIResponseObjectProvider,
        CacheService $cacheService
    ) {
        $this->deliveryAPIResponseObjectProvider = $deliveryAPIResponseObjectProvider;
        $this->cacheService = $cacheService;
    }

    public function getResponseObject(string $subdomain, string $path): DeliveryAPIResponseObject
    {
        $result = $this->cacheService->get($subdomain, $path);
        if ($result !== null) {
            return $result;
        }

        $result = $this->deliveryAPIResponseObjectProvider->getResponseObject($subdomain, $path);
        if ($result->cache === true) {
            $this->cacheService->set($subdomain, $path, $result);
        }

        return $result;
    }
}
