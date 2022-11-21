<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\Controller;

use Hyvor\BlogBundle\Exception\UnknownSubdomainException;
use Hyvor\BlogBundle\Service\Cache\CacheService;
use Hyvor\BlogBundle\Service\Configuration\Registry\ConfigurationRegistry;
use Hyvor\BlogBundle\Service\Request\RequestValidationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    public const RESPONSE_MESSAGE = 'OK';
    public const RESPONSE_MESSAGE_GENERAL_ERROR = 'Error';
    public const RESPONSE_MESSAGE_INVALID_SUBDOMAIN = 'Invalid subdomain';
    public const RESPONSE_MESSAGE_INVALID_WEBHOOK = 'Unable to validate webhook';

    /**
     * @var ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var RequestValidationService
     */
    private $requestValidationService;

    /**
     * @var CacheService
     */
    private $cacheService;

    public function __construct(
        ConfigurationRegistry $configurationRegistry,
        RequestValidationService $requestValidationService,
        CacheService $cacheService
    ) {
        $this->configurationRegistry = $configurationRegistry;
        $this->requestValidationService = $requestValidationService;
        $this->cacheService = $cacheService;
    }

    public function __invoke(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);
        $subdomain = $payload['subdomain'] ?? null;
        if ($subdomain === null) {
            return new Response(self::RESPONSE_MESSAGE_INVALID_SUBDOMAIN);
        }

        try {
            $configuration = $this->configurationRegistry->getConfiguration($payload['subdomain']);
            if (!$this->requestValidationService->validate($request, $configuration->getWebhookSecret())) {
                return new Response(self::RESPONSE_MESSAGE_INVALID_WEBHOOK);
            }

            $event = $payload['event'] ?? null;
            if ($event === 'cache.single' && isset($payload['data']['path'])) {
                $this->cacheService->clearSingleCache($subdomain, $payload['data']['path']);
            } elseif ($event === 'cache.templates') {
                $this->cacheService->clearTemplateCache($subdomain);
            } elseif ($event === 'cache.all') {
                $this->cacheService->clearAllCache($subdomain);
            }
        } catch (UnknownSubdomainException $e) {
            return new Response(self::RESPONSE_MESSAGE_INVALID_SUBDOMAIN);
        } catch (\Exception $e) {
            return new Response(self::RESPONSE_MESSAGE_GENERAL_ERROR);
        }

        return new Response(self::RESPONSE_MESSAGE);
    }
}
