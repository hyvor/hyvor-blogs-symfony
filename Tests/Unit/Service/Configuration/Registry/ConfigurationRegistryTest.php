<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Unit\Service\Configuration\Registry;

use Hyvor\BlogsBundle\Configuration\Configuration;
use Hyvor\BlogsBundle\Exception\UnknownSubdomainException;
use Hyvor\BlogsBundle\Service\Configuration\Registry\ConfigurationRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ConfigurationRegistryTest extends TestCase
{
    /**
     * @var Configuration|ObjectProphecy
     */
    private $configurationProphecy;

    /**
     * @var ConfigurationRegistry
     */
    private $configurationRegistry;

    protected function setUp(): void
    {
        $this->configurationProphecy = $this->prophesize(Configuration::class);
        $this->configurationRegistry = new ConfigurationRegistry();
        $this->configurationProphecy->getSubdomain()
            ->willReturn('foo')
            ->shouldBeCalled();
        $this->configurationRegistry->addConfiguration($this->configurationProphecy->reveal());
    }

    public function testGetConfigurationInvalidSubdomain(): void
    {
        $this->expectException(UnknownSubdomainException::class);
        $this->configurationRegistry->getConfiguration('bar');
    }

    public function testGetConfiguration(): void
    {
        self::assertSame(
            $this->configurationProphecy->reveal(),
            $this->configurationRegistry->getConfiguration('foo')
        );
    }
}
