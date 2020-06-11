<?php

namespace Apl\HotelsDbBundle\Service\EntityVersion;


use Apl\HotelsDbBundle\Entity\AbstractAlias;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerProxy;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ArrayGetterMapped;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ObjectDataManipulator;
use Doctrine\ORM\EntityManagerInterface;


class TestVersion implements EntityVersionInterface {
    /**
     * @return VersionedEntityInterface|null
     */
    public function getEntity(): ?VersionedEntityInterface
    {
        // TODO: Implement getEntity() method.
    }

    /**
     * @param VersionedEntityInterface $entity
     * @return $this
     */
    public function setEntity(VersionedEntityInterface $entity): EntityVersionInterface
    {
        return $this;
    }

    /**
     * @return AbstractAlias|null
     */
    public function getResponsibleAlias(): ?AbstractAlias
    {
        // TODO: Implement getResponsibleAlias() method.
    }

    /**
     * @param AbstractAlias $alias
     * @return $this
     */
    public function setResponsibleAlias(AbstractAlias $alias): EntityVersionInterface
    {
        return $this;
    }

    public function getGetterMapping(): GetterMapping
    {
        // TODO: Implement getGetterMapping() method.
    }

    public function getSetterMapping(): SetterMapping
    {
        // TODO: Implement getSetterMapping() method.
    }
}
abstract class TestVersionedEntity implements VersionedEntityInterface
{
    public static function getVersionClassName(): string
    {
        return TestVersion::class;
    }
}

class EntityVersionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectDataManipulator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectDataManipulatorMock;

    /**
     * @var EntityManagerInterface|EntityManagerProxy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var EntityVersionManager
     */
    private $entityVersionManager;

    public function setUp()
    {
        $this->objectDataManipulatorMock = $this->createMock(ObjectDataManipulator::class);
        $this->entityManagerMock = $this->createMock(EntityManagerProxy::class);

        $this->entityVersionManager = new EntityVersionManager();
        $this->entityVersionManager->setEntityManager($this->entityManagerMock);
        $this->entityVersionManager->setObjectDataManipulator($this->objectDataManipulatorMock);
    }

    /**
     * @group entity_version_manager
     */
    public function testCreateVersion()
    {
        $entityClass = $this->getMockForAbstractClass(TestVersionedEntity::class);

        $this->objectDataManipulatorMock->expects($this->at(0))->method('hydrate')->with($this->isInstanceOf(TestVersion::class), $entityClass);
        $this->objectDataManipulatorMock->expects($this->at(1))->method('hydrate')->willReturnCallback(function($destination, $source) {
            $this->assertInstanceOf(TestVersion::class, $destination);
            $this->assertInstanceOf(ArrayGetterMapped::class, $source);

            $sourceReflection = new \ReflectionObject($source);
            $sourceDataProperty = $sourceReflection->getProperty('data');
            $sourceDataProperty->setAccessible(true);

            $this->assertSame($sourceDataProperty->getValue($source), ['testAttr' => 'testValue']);
        });

        $this->entityVersionManager->createVersion($entityClass, new ArrayGetterMapped(['testAttr' => 'testValue']));
    }

    /**
     * @group entity_version_manager
     */
    public function testUseVersionAsActive()
    {
        $entityClass = $this->getMockForAbstractClass(VersionedEntityInterface::class);
        $entityClass->expects($this->never())->method('getVersionClassName');

        $entityVersionClass = $this->getMockForAbstractClass(EntityVersionInterface::class);
        $entityVersionClass->expects($this->atLeastOnce())->method('getEntity')->willReturn($entityClass);

        $this->objectDataManipulatorMock->expects($this->once())->method('hydrate')->willReturnCallback(function($destination, $source) use ($entityVersionClass, $entityClass) {
            $this->assertSame($entityClass, $destination);
            $this->assertSame($entityVersionClass, $source);
        });

        $this->entityVersionManager->useVersionAsActive($entityVersionClass);
    }

    /**
     * @group entity_version_manager
     */
    public function testIsHasVersionWithData()
    {
        // Without active version
        $createdVersion = $this->getMockForAbstractClass(EntityVersionInterface::class);
        $entity = $this->getMockForAbstractClass(VersionedEntityInterface::class);
        $entity->expects($this->once())->method('getActiveVersion')->willReturn(null);

        $createdVersion->expects($this->atLeastOnce())->method('getEntity')->willReturn($entity);

        $this->assertFalse($this->entityVersionManager->isHasSameVersion($createdVersion));

        // With active version
        $createdVersion = $this->getMockForAbstractClass(EntityVersionInterface::class);
        $currentActiveVersion = $this->getMockForAbstractClass(EntityVersionInterface::class);

        $entity = $this->getMockForAbstractClass(VersionedEntityInterface::class);
        $entity->expects($this->atLeastOnce())->method('getActiveVersion')->willReturn($currentActiveVersion);

        $createdVersion->expects($this->atLeastOnce())->method('getEntity')->willReturn($entity);
        $currentActiveVersion->expects($this->never())->method('getEntity')->willReturn($entity);

        $this->objectDataManipulatorMock->expects($this->once())->method('compare')->willReturnCallback(function($sourceOne, $sourceTwo) use ($currentActiveVersion, $createdVersion) {
            $this->assertSame($currentActiveVersion, $sourceOne);
            $this->assertSame($createdVersion, $sourceTwo);
            return true;
        });

        $this->assertTrue($this->entityVersionManager->isHasSameVersion($createdVersion));
    }
}
