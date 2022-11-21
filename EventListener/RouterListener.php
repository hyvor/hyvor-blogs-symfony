<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class RouterListener
{
    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(RequestMatcherInterface $requestMatcher, LoggerInterface $logger)
    {
        $this->requestMatcher = $requestMatcher;
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        try {
            $parameters = $this->requestMatcher->matchRequest($request);
            $this->logger->info('Matched route "{route}".', [
                'route' => $parameters['_route'] ?? 'n/a',
                'route_parameters' => $parameters,
                'request_uri' => $request->getUri(),
                'method' => $request->getMethod(),
            ]);
            $request->attributes->add($parameters);
            unset($parameters['_route'], $parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (ResourceNotFoundException $resourceNotFoundException) {
        }
    }
}
