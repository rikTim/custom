<?php

namespace Base\HotelsDbBundle;

use Base\HotelsDbBundle\DependencyInjection\Compiler\CDNManagerPass;
use Base\HotelsDbBundle\DependencyInjection\Compiler\ObjectDataManipulatorPass;
use Base\HotelsDbBundle\DependencyInjection\Compiler\ServiceProviderManagerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HotelsDbBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ServiceProviderManagerPass())
            ->addCompilerPass(new CDNManagerPass())
            ->addCompilerPass(new ObjectDataManipulatorPass());

    }
}
