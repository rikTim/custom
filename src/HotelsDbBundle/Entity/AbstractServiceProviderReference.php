<?php


namespace Apl\HotelsDbBundle\Entity;


use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractServiceProviderReference
 * @package Apl\HotelsDbBundle\Entity
 *
 * @ORM\MappedSuperclass()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="ENTITY_ID_IDX", columns={"entity_id"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(name="MAPPING_SP_REFERENCE", columns={"alias", "reference", "entity_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class AbstractServiceProviderReference implements ServiceProviderReferenceInterface
{
    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait;

    /**
     * WARNING! This field need redeclaration in children class for correct loading with doctrine
     * @var ServiceProviderReferencedEntityInterface
     */
    protected $entity;

    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\ServiceProviderAlias", columnPrefix=false)
     * @var ServiceProviderAlias
     */
    private $alias;

    /**
     * @ORM\Column(type="string", nullable=false, length=255)
     * @var string
     */
    private $reference;

    /**
     * @ORM\Column(name="type", type="integer", length=1)
     * @var int
     */
    private $type;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->getAlias()}/{$this->getReference()}";
    }

    /**
     * @return ServiceProviderReferencedEntityInterface
     */
    public function getEntity(): ?ServiceProviderReferencedEntityInterface
    {
        return $this->entity;
    }

    /**
     * @param ServiceProviderReferencedEntityInterface $entity
     * @return ServiceProviderReferenceInterface
     */
    public function setEntity(ServiceProviderReferencedEntityInterface $entity): ServiceProviderReferenceInterface
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return ServiceProviderAlias
     */
    public function getAlias(): ?ServiceProviderAlias
    {
        return $this->alias;
    }

    /**
     * @param ServiceProviderAlias $alias
     * @return $this
     */
    public function setAlias(ServiceProviderAlias $alias): ServiceProviderReferenceInterface
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return $this
     */
    public function setReference(string $reference): ServiceProviderReferenceInterface
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): ServiceProviderReferenceInterface
    {
        $this->type = $type;
        return $this;
    }
}