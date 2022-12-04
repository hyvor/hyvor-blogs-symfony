<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Service\Configuration\Registry;

use Hyvor\BlogsBundle\Configuration\Configuration;
use Hyvor\BlogsBundle\Exception\UnknownSubdomainException;

class ConfigurationRegistry
{
    /**
     * @var array<string, Configuration>
     */
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
