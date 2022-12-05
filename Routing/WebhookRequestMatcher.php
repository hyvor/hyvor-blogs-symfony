<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Routing;

use Hyvor\BlogsBundle\Controller\WebhookController;
use Hyvor\BlogsBundle\Exception\UnknownSubdomainException;
use Hyvor\BlogsBundle\Service\Configuration\Registry\ConfigurationRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class WebhookRequestMatcher implements RequestMatcherInterface
{
    /**
     * @var ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var string
     */
    private $webhookPath;

    /**
     * @var string
     */
    private $httpMethod;

    public function __construct(ConfigurationRegistry $configurationRegistry, string $webhookPath, string $httpMethod)
    {
        $this->configurationRegistry = $configurationRegistry;
        $this->webhookPath = $webhookPath;
        $this->httpMethod = $httpMethod;
    }

    public function matchRequest(Request $request): array
    {
        if ($request->getPathInfo() !== $this->webhookPath || $request->getRealMethod() !== $this->httpMethod) {
            throw new ResourceNotFoundException();
        }

        try {
            $this->configurationRegistry->getConfiguration($request->getHost());
            return [
                '_controller' => WebhookController::class,
            ];
        } catch (UnknownSubdomainException $exception) {
        }

        throw new ResourceNotFoundException();
    }
}
