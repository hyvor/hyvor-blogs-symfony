<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\DependencyInjection\CompilerPass;

use Hyvor\BlogBundle\Configuration\Configuration as BlogConfiguration;
use Hyvor\BlogBundle\DependencyInjection\Configuration;
use Hyvor\BlogBundle\DependencyInjection\HyvorBlogExtension;
use Hyvor\BlogBundle\Service\Cache\CacheRegistry;
use Hyvor\BlogBundle\Service\Configuration\Registry\ConfigurationRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $blogConfigurationRegistryDefinition = $container->getDefinition(ConfigurationRegistry::class);
        $cacheRegistryDefinition = $container->getDefinition(CacheRegistry::class);
        $blogConfigurations = $container->getParameter(HyvorBlogExtension::PARAMETER_BLOGS);
        foreach ($blogConfigurations as $blogConfiguration) {
            $cachePoolDefinition = $this->getCachePoolDefinition(
                $container,
                    $blogConfiguration[Configuration::KEY_BLOGS_CACHE_POOL] ?? null
            );
            $cacheRegistryDefinition->addMethodCall(
                'addCachePool',
                [
                    $blogConfiguration[Configuration::KEY_BLOGS_SUBDOMAIN],
                    $cachePoolDefinition,
                ]
            );
            $configurationDefinition = new Definition(
                BlogConfiguration::class,
                [
                    $blogConfiguration[Configuration::KEY_BLOGS_DELIVERY_API_KEY],
                    $blogConfiguration[Configuration::KEY_BLOGS_WEBHOOK_SECRET],
                    $blogConfiguration[Configuration::KEY_BLOGS_SUBDOMAIN],
                    $blogConfiguration[Configuration::KEY_BLOGS_BASE_PATH],
                ]
            );
            $blogConfigurationRegistryDefinition->addMethodCall('addConfiguration', [$configurationDefinition]);
        }
    }

    private function getCachePoolDefinition(ContainerBuilder $containerBuilder, ?string $cachePool): Definition
    {
        if ($cachePool === null) {
            $cachePool = $containerBuilder->getParameter(HyvorBlogExtension::PARAMETER_DEFAULT_CACHE_POOL);
        }
        if (!$containerBuilder->hasDefinition($cachePool)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Default cache pool "%s" is not defined',
                    $containerBuilder->getParameter(
                        HyvorBlogExtension::PARAMETER_DEFAULT_CACHE_POOL
                    )
                )
            );
        }
        $definition = $containerBuilder->getDefinition($cachePool);
        $parentDefinition = $this->getParentDefinition($containerBuilder, $definition);
        if (!is_subclass_of($parentDefinition->getClass(), CacheItemPoolInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cache pool "%s" is not an instance of "%s"',
                    $cachePool,
                    CacheItemPoolInterface::class
                )
            );
        }

        return $definition;
    }

    private function getParentDefinition(ContainerBuilder $containerBuilder, Definition $definition): Definition
    {
        if ($definition instanceof ChildDefinition) {
            return $this->getParentDefinition(
                $containerBuilder,
                $containerBuilder->getDefinition($definition->getParent())
            );
        }

        return $definition;
    }
}
