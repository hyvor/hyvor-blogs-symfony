<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class HyvorBlogsExtension extends Extension
{
    public const PARAMETER_BLOGS = 'hyvor_blog.config.blogs';
    public const PARAMETER_BLOGS_BASE_URL = 'hyvor_blog.config.blogs_base_url';
    public const PARAMETER_WEBHOOK_PATH = 'hyvor_blog.config.webhook_path';
    public const PARAMETER_DEFAULT_CACHE_POOL = 'hyvor_blog.config.default_cache_pool';

    /**
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $container->setParameter(self::PARAMETER_BLOGS, $config[Configuration::SECTION_BLOGS]);
        $container->setParameter(
            self::PARAMETER_WEBHOOK_PATH,
            $config[Configuration::SECTION_WEBHOOK][Configuration::KEY_WEBHOOK_PATH]
        );
        $container->setParameter(
            self::PARAMETER_DEFAULT_CACHE_POOL,
            $config[Configuration::KEY_DEFAULT_CACHE_POOL]
        );
        $container->setParameter(
            self::PARAMETER_BLOGS_BASE_URL,
            $config[Configuration::KEY_BLOGS_BASE_URL]
        );
    }
}
