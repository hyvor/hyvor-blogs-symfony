<?php

declare(strict_types=1);

namespace Hyvor\BlogsBundle;

use Hyvor\BlogsBundle\DependencyInjection\CompilerPass\ConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HyvorBlogsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ConfigurationPass());
    }
}
