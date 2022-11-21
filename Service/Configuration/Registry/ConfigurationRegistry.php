<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\Service\Configuration\Registry;

use Hyvor\BlogBundle\Configuration\Configuration;
use Hyvor\BlogBundle\Exception\UnknownSubdomainException;

class ConfigurationRegistry
{
    private $blogConfigurations = [];

    public function getConfiguration(string $subdomain): Configuration
    {
        $blogConfiguration = $this->blogConfigurations[$subdomain] ?? null;
        if (!$blogConfiguration) {
            throw new UnknownSubdomainException(sprintf('Unknown subdomain "%s"', $subdomain));
        }

        return $blogConfiguration;
    }

    public function addConfiguration(Configuration $blogConfiguration): void
    {
        $this->blogConfigurations[$blogConfiguration->getSubdomain()] = $blogConfiguration;
    }
}
