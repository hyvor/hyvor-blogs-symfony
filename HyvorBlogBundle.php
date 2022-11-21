<?php

declare(strict_types=1);

namespace Hyvor\BlogBundle;

use Hyvor\BlogBundle\DependencyInjection\CompilerPass\ConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HyvorBlogBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ConfigurationPass());
    }
}
