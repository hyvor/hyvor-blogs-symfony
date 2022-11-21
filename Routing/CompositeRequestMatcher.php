<?php

namespace Hyvor\BlogBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class CompositeRequestMatcher implements RequestMatcherInterface
{
    private $matchers = [];

    public function matchRequest(Request $request): array
    {
        foreach ($this->matchers as $matcher) {
            try {
                return $matcher->matchRequest($request);
            } catch (ResourceNotFoundException $e) {
            }
        }

        throw new ResourceNotFoundException();
    }

    public function addRequestMatcher(RequestMatcherInterface $matcher)
    {
        $this->matchers[] = $matcher;
    }
}
