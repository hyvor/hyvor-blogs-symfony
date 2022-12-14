<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle\Tests\Functional;

use Hyvor\BlogsBundle\HyvorBlogsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new HyvorBlogsBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $_SERVER['APP_ENV'] = 'test';
        $container->loadFromExtension(
            'framework',
            [
                'router' => [
                    'utf8' => true,
                ],
                'test' => true,
                'http_method_override' => false,
            ]
        );
        $container->loadFromExtension(
            'hyvor_blogs',
            [
                'webhook_path' => '/hyvorblogs/webhook',
                'blogs' => [
                    [
                        'subdomain' => 'localhost',
                        'base_path' => '/blog',
                        'cache_pool' => 'cache.app',
                        'delivery_api_key' => 'your-delivery-api-key',
                        'webhook_secret' => 'your-webhook-secret',
                    ],
                ],
            ]
        );
    }
}
