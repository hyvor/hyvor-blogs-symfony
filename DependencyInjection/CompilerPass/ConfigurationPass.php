<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\DependencyInjection\CompilerPass;

use Hyvor\BlogsBundle\Configuration\Configuration as BlogConfiguration;
use Hyvor\BlogsBundle\DependencyInjection\Configuration;
use Hyvor\BlogsBundle\DependencyInjection\HyvorBlogsExtension;
use Hyvor\BlogsBundle\Service\Cache\CacheRegistry;
use Hyvor\BlogsBundle\Service\Configuration\Registry\ConfigurationRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfigurationPass implements CompilerPassInterface
{
    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $blogConfigurationRegistryDefinition = $container->getDefinition(ConfigurationRegistry::class);
        $cacheRegistryDefinition = $container->getDefinition(CacheRegistry::class);
        /** @var array<string, array<string, string>> $blogConfigurations */
        $blogConfigurations = $container->getParameter(HyvorBlogsExtension::PARAMETER_BLOGS);
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
            /** @var string $cachePool */
            $cachePool = $containerBuilder->getParameter(HyvorBlogsExtension::PARAMETER_DEFAULT_CACHE_POOL);
        }
        if (!$containerBuilder->hasDefinition($cachePool)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cache pool "%s" is not defined',
                    $cachePool
                )
            );
        }
        $definition = $containerBuilder->getDefinition($cachePool);
        $parentDefinition = $this->getParentDefinition($containerBuilder, $definition);
        $parentDefinitionClass = $parentDefinition->getClass();
        if ($parentDefinitionClass === null) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Definition "%s" has no class',
                    $cachePool
                )
            );
        }
        if (!is_subclass_of($parentDefinitionClass, CacheItemPoolInterface::class)) {
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
