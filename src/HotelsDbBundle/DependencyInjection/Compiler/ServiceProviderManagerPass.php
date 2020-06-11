<?php

namespace Apl\HotelsDbBundle\DependencyInjection\Compiler;


use Apl\HotelsDbBundle\Service\ServiceProvider\ServiceProviderManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


class ServiceProviderManagerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ServiceProviderManager::class)) {
            return;
        }

        $definition = $container->findDefinition(ServiceProviderManager::class);
        foreach ($container->findTaggedServiceIds('hotels_db.service_provider') as $id => $tags) {
            $definition->addMethodCall('addServiceProvider', [new Reference($id)]);
        }
    }
}