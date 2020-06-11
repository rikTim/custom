<?php

namespace Apl\HotelsDbBundle\DependencyInjection\Compiler;


use Apl\HotelsDbBundle\Service\CDN\CDNManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Class CDNManagerPass
 *
 * @package Apl\HotelsDbBundle\DependencyInjection\Compiler
 */
class CDNManagerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(CDNManager::class)) {
            return;
        }

        $definition = $container->findDefinition(CDNManager::class);
        foreach ($container->findTaggedServiceIds('hotels_db.cdn') as $id => $tags) {
            $definition->addMethodCall('addCDN', [new Reference($id)]);
        }
    }
}