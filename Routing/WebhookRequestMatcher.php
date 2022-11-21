<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\Routing;

use Hyvor\BlogBundle\Controller\WebhookController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class WebhookRequestMatcher implements RequestMatcherInterface
{
    /**
     * @var string
     */
    private $webhookPath;

    /**
     * @var string
     */
    private $httpMethod;

    public function __construct(string $webhookPath, string $httpMethod)
    {
        $this->webhookPath = $webhookPath;
        $this->httpMethod = $httpMethod;
    }

    public function matchRequest(Request $request): array
    {
        if ($request->getPathInfo() === $this->webhookPath && $request->getRealMethod() === $this->httpMethod) {
            return [
                '_controller' => WebhookController::class,
            ];
        }

        throw new ResourceNotFoundException();
    }
}
