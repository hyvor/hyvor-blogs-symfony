<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\Service\Response\Provider;

class RequestUrlProvider
{
    /**
     * @var string
     */
    private $urlTemplate;

    public function __construct(string $urlTemplate)
    {
        $this->urlTemplate = $urlTemplate;
    }

    public function getRequestUrl(string $subdomain) : string
    {
        return sprintf($this->urlTemplate, $subdomain);
    }
}
