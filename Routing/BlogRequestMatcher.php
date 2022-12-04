<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Routing;

use Hyvor\BlogsBundle\Controller\BlogController;
use Hyvor\BlogsBundle\Exception\UnknownSubdomainException;
use Hyvor\BlogsBundle\Service\Configuration\Registry\ConfigurationRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class BlogRequestMatcher implements RequestMatcherInterface
{
    /**
     * @var ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var string
     */
    private $httpMethod;

    public function __construct(ConfigurationRegistry $configurationRegistry, string $httpMethod)
    {
        $this->configurationRegistry = $configurationRegistry;
        $this->httpMethod = $httpMethod;
    }

    public function matchRequest(Request $request): array
    {
        if ($request->getRealMethod() !== $this->httpMethod) {
            throw new ResourceNotFoundException();
        }
        try {
            $blogConfiguration = $this->configurationRegistry->getConfiguration($request->getHost());
        } catch (UnknownSubdomainException $exception) {
            throw new ResourceNotFoundException();
        }

        $path = $request->getPathInfo();
        $basePath = $blogConfiguration->getBasePath();
        // Root path
        if ($path === $basePath) {
            return [
                '_controller' => BlogController::class,
                'path' => '/',
            ];
        }

        // Add trailing slash to path - the base path fragment must be matched exactly
        $basePath .= '/';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
            return [
                '_controller' => BlogController::class,
                'path' => '/' . $path,
            ];
        }

        // Path fragment was not matched exactly
        throw new ResourceNotFoundException();
    }
}
