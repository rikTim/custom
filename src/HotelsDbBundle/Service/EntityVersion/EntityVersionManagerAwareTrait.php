<?php


namespace Apl\HotelsDbBundle\Service\EntityVersion;


trait EntityVersionManagerAwareTrait
{
    /**
     * @var EntityVersionManager
     */
    protected $entityVersionManager;

    /**
     * @param EntityVersionManager $entityVersionManager
     * @required
     */
    public function setEntityVersionManager(EntityVersionManager $entityVersionManager): void
    {
        $this->entityVersionManager = $entityVersionManager;
    }
}