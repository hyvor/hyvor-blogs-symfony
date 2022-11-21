<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const SECTION_BLOGS = 'blogs';
    public const SECTION_WEBHOOK = 'webhook';
    public const KEY_BLOGS_SUBDOMAIN = 'subdomain';
    public const KEY_BLOGS_BASE_PATH = 'base_path';
    public const KEY_BLOGS_CACHE_POOL = 'cache_pool';
    public const KEY_BLOGS_DELIVERY_API_KEY = 'delivery_api_key';
    public const KEY_BLOGS_WEBHOOK_SECRET = 'webhook_secret';
    public const KEY_WEBHOOK_PATH = 'path';
    public const KEY_DEFAULT_CACHE_POOL = 'default_cache_pool';
    public const KEY_BLOGS_BASE_URL = 'blogs_base_url';

    private const DEFAULT_CACHE_POOL = 'cache.app';
    private const WEBHOOK_PATH = '/hyvorblogs/webhook';
    private const BLOGS_BASE_URL = 'https://blogs.hyvor.com';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('hyvor_blog');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode(self::KEY_DEFAULT_CACHE_POOL)
                ->defaultValue(self::DEFAULT_CACHE_POOL)
                ->cannotBeEmpty()
            ->end()
            ->scalarNode(self::KEY_BLOGS_BASE_URL)
                ->defaultValue(self::BLOGS_BASE_URL)
                ->cannotBeEmpty()
            ->end()
            ->arrayNode(self::SECTION_BLOGS)
                ->useAttributeAsKey(self::KEY_BLOGS_SUBDOMAIN, false)
                ->arrayPrototype()
                    ->normalizeKeys(false)
                    ->children()
                        ->scalarNode(self::KEY_BLOGS_SUBDOMAIN)
                        ->end()
                        ->scalarNode(self::KEY_BLOGS_BASE_PATH)
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode(self::KEY_BLOGS_CACHE_POOL)
                            ->defaultNull()
                        ->end()
                        ->scalarNode(self::KEY_BLOGS_DELIVERY_API_KEY)
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode(self::KEY_BLOGS_WEBHOOK_SECRET)
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode(self::SECTION_WEBHOOK)
                ->children()
                    ->scalarNode(self::KEY_WEBHOOK_PATH)
                        ->defaultValue(self::WEBHOOK_PATH)
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
