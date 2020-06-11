<?php


namespace Base\HotelsDbBundle\Service\EntityVersion;


use Base\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;

interface VersionedEntityInterface
{
    /**
     * @return string
     */
    public static function getVersionClassName(): string;

    /**
     * @return EntityVersionInterface|null
     */
    public function getActiveVersion(): ?EntityVersionInterface;

    /**
     * @param EntityVersionInterface $version
     * @return VersionedEntityInterface
     * @throws InvalidArgumentException
     */
    public function setActiveVersion(EntityVersionInterface $version): VersionedEntityInterface;
}