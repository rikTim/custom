<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Apl\HotelsDbBundle\Entity\AbstractAlias;
use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Exception\LogicException;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\VersionedEntityInterface;

trait EntityVersionTrait
{
    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\ServiceProviderAlias", columnPrefix="responsible_")
     * @var ServiceProviderAlias
     */
    private $responsibleAlias;

    /**
     * @return VersionedEntityInterface
     */
    public function getEntity(): ?VersionedEntityInterface
    {
        return $this->entity;
    }

    /**
     * @param VersionedEntityInterface $entity
     * @return self|EntityVersionInterface
     * @throws \Apl\HotelsDbBundle\Exception\LogicException
     * @throws \Apl\HotelsDbBundle\Exception\InvalidArgumentException
     */
    public function setEntity(VersionedEntityInterface $entity): EntityVersionInterface
    {
        $entityVersionClassName = $entity::getVersionClassName();
        if (!($this instanceof $entityVersionClassName)) {
            throw new InvalidArgumentException(
                sprintf('Incorrect entity "%s" for version "%s"', \get_class($entity), \get_class($this))
            );
        }

        if (!property_exists($this, 'entity')) {
            throw new LogicException(
                sprintf('Incorrect entity version "%s": version must has protected property "entity"', \get_class($this))
            );
        }
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return ServiceProviderAlias|null
     */
    public function getResponsibleAlias(): ?AbstractAlias
    {
        return $this->responsibleAlias;
    }

    /**
     * @param AbstractAlias $alias
     * @return self|EntityVersionInterface
     * @throws \Apl\HotelsDbBundle\Exception\InvalidArgumentException
     */
    public function setResponsibleAlias(AbstractAlias $alias): EntityVersionInterface
    {
        if (!($alias instanceof ServiceProviderAlias)) {
            throw new InvalidArgumentException(
                sprintf('Incorrect responsible alias "%s" for version "%s"', \get_class($alias), \get_class($this))
            );
        }

        $this->responsibleAlias = $alias;
        return $this;
    }
}