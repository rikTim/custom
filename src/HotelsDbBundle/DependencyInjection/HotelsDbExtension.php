<?php

namespace Apl\HotelsDbBundle\DependencyInjection;

use Apl\HotelsDbBundle\Service\CDN\CDNInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\ComparatorInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\HydratorInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ServiceProviderInterface;
use Doctrine\Common\EventSubscriber;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class HotelsDbExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ServiceProviderInterface::class)
            ->addTag('hotels_db.service_provider');

        $container->registerForAutoconfiguration(CDNInterface::class)
            ->addTag('hotels_db.cdn');

        $container->registerForAutoconfiguration(HydratorInterface::class)
            ->addTag('hotels_db.hydrator');

        $container->registerForAutoconfiguration(ComparatorInterface::class)
            ->addTag('hotels_db.comparator');

        $container->registerForAutoconfiguration(EventSubscriber::class)
            ->addTag('doctrine.event_subscriber');

        $container->registerForAutoconfiguration(AbstractAdminExtension::class)
            ->addTag('sonata.admin.extension', ['global' => true]);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('hotels_db.default_locale', $config['default_locale']);
        $container->setParameter('hotels_db.translate_fallback', $config['translate_fallback']);
        $container->setParameter('hotels_db.entity_manager', $config['entity_manager'] ?? 'default');

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
