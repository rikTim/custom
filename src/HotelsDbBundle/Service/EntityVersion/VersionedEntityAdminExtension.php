<?php


namespace Base\HotelsDbBundle\Service\EntityVersion;


use Base\HotelsDbBundle\Entity\ServiceProviderAlias;
use Base\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

class VersionedEntityAdminExtension extends AbstractAdminExtension
{
    use EntityVersionManagerAwareTrait,
        EntityManagerAwareTrait;

    /**
     * @param AdminInterface $admin
     * @param $object
     */
    public function preUpdate(AdminInterface $admin, $object): void
    {
        if ($object instanceof VersionedEntityInterface) {
            $this->entityVersionManager->createVersionFromEntity($object, new ServiceProviderAlias('sonata-admin'));
        }
    }
}