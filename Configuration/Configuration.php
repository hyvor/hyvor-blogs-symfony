<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Configuration;

class Configuration
{
    /**
     * @var string
     */
    private $deliveryApiKey;

    /**
     * @var string
     */
    private $webhookSecret;

    /**
     * @var string
     */
    private $subdomain;

    /**
     * @var string
     */
    private $basePath;

    public function __construct(
        string $deliveryApiKey,
        string $webhookSecret,
        string $subdomain,
        string $basePath
    ) {
        $this->deliveryApiKey = $deliveryApiKey;
        $this->webhookSecret = $webhookSecret;
        $this->subdomain = $subdomain;
        $this->basePath = $basePath;
    }

    public function getDeliveryApiKey(): string
    {
        return $this->deliveryApiKey;
    }

    public function getWebhookSecret(): string
    {
        return $this->webhookSecret;
    }

    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
