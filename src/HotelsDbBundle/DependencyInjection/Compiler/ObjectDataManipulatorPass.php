<?php

namespace Apl\HotelsDbBundle\DependencyInjection\Compiler;


use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ObjectDataManipulator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


class ObjectDataManipulatorPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ObjectDataManipulator::class)) {
            return;
        }

        $definition = $container->findDefinition(ObjectDataManipulator::class);
        foreach ($container->findTaggedServiceIds('hotels_db.hydrator') as $id => $tags) {
            $definition->addMethodCall('addHydrator', [new Reference($id)]);
        }

        foreach ($container->findTaggedServiceIds('hotels_db.comparator') as $id => $tags) {
            $definition->addMethodCall('addComparator', [new Reference($id)]);
        }
    }
}