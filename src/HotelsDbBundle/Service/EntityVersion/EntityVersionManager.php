<?php


namespace Apl\HotelsDbBundle\Service\EntityVersion;


use Apl\HotelsDbBundle\Entity\AbstractAlias;
use Apl\HotelsDbBundle\Exception\LogicException;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ArrayGetterMapped;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ObjectDataManipulatorAwareTrait;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class EntityVersionManager
 * @package Apl\HotelsDbBundle\Service
 */
class EntityVersionManager
{
    use EntityManagerAwareTrait,
        ObjectDataManipulatorAwareTrait;

    /**
     * @param VersionedEntityInterface $entity
     * @param AbstractAlias|null $responsibleAlias
     * @return EntityVersionInterface|null
     * @throws \Doctrine\ORM\ORMException
     */
    public function createVersionFromEntity(
        VersionedEntityInterface $entity,
        AbstractAlias $responsibleAlias = null
    ): ?EntityVersionInterface
    {
        if ($entity->getActiveVersion() && $this->objectDataManipulator->compare($entity->getActiveVersion(), $entity)) {
            return null;
        }

        $version = $this->createVersion($entity, $entity, $responsibleAlias);
        $this->useVersionAsActive($version);
        $this->entityManager->persist($version);

        return $version;
    }

    /**
     * @param VersionedEntityInterface $entity
     * @param HasGetterMappingInterface|array $data
     * @param AbstractAlias|null $responsibleAlias
     * @return EntityVersionInterface
     */
    public function createVersion(VersionedEntityInterface $entity, HasGetterMappingInterface $data, AbstractAlias $responsibleAlias = null): EntityVersionInterface
    {
        $versionClassName = $this->getVersionClassName($entity);

        /** @var EntityVersionInterface $version */
        $version = new $versionClassName;

        $this->objectDataManipulator->hydrate($version, $entity);
        if ($entity !== $data) {
            $this->objectDataManipulator->hydrate($version, $data);
        }

        $version->setEntity($entity);
        if ($responsibleAlias) {
            $version->setResponsibleAlias($responsibleAlias);
        }

        return $version;
    }

    /**
     * @param EntityVersionInterface $version
     */
    public function useVersionAsActive(EntityVersionInterface $version): void
    {
        if (!$version->getEntity()) {
            throw new RuntimeException('Can`t use version as active when version not have entity');
        }

        $this->objectDataManipulator->hydrate($version->getEntity(), $version);
        $version->getEntity()->setActiveVersion($version);
    }

    /**
     * @param EntityVersionInterface $version
     * @return bool
     */
    public function isHasSameVersion(EntityVersionInterface $version): bool
    {
        $entity = $version->getEntity();
        // Если новые данные уже есть в текущей активной версии
        if ($entity->getActiveVersion() && $this->objectDataManipulator->compare($entity->getActiveVersion(), $version)) {
            return true;
        }

        // Если новые данные уже есть в последней версии от конкретного ответственного сервиса
        if ($version->getResponsibleAlias()) {
            $versionClassName = $this->getVersionClassName($entity);
            $lastServiceProviderVersion = $this->getVersionRepository($versionClassName)->findLastVersionByResponsible($entity, $version->getResponsibleAlias());
            if ($lastServiceProviderVersion && $this->objectDataManipulator->compare($lastServiceProviderVersion, $version)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param VersionedEntityInterface $entity
     * @return string
     */
    private function getVersionClassName(VersionedEntityInterface $entity) : string
    {
        $className = $entity::getVersionClassName();
        if (!class_exists($className) || !is_subclass_of($className, EntityVersionInterface::class)) {
            throw new LogicException(
                sprintf(
                    'Versioned entity of class "%s" has return incorrect version class name "%s"',
                    get_class($entity),
                    $className
                )
            );
        }

        return $className;
    }

    /**
     * @param string $versionClassName
     * @return EntityVersionRepositoryInterface
     */
    private function getVersionRepository(string $versionClassName) : EntityVersionRepositoryInterface
    {
        $repository = $this->entityManager->getRepository($versionClassName);
        if (!($repository instanceof EntityVersionRepositoryInterface)) {
            throw new LogicException(
                sprintf(
                    'Entity version "%s" has incorrect repository "%s"',
                    $versionClassName,
                    get_class($repository)
                )
            );
        }

        return $repository;
    }
}