<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;

use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\VersionedEntityInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FacilityGroup
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\FacilityGroupRepository")
 * @ORM\Table(name="hotels_db_dictionary_facility_group", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),
 *     })
 * @ORM\HasLifecycleCallbacks()
 */
class FacilityGroup extends  AbstractFacilityGroup implements ServiceProviderReferencedEntityInterface,VersionedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;


    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    private $priory;

    /**
     * @ORM\Column(type="string", length=35, nullable=true)
     */
    private $alias;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\FacilityGroupSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var FacilityGroupSPReference[]|ArrayCollection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\FacilityGroupVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var FacilityGroupVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return FacilityGroupVersion::class;
    }

    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
    }

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_RESET)
        );
    }

    /**
     * @return FacilityGroupSPReference[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
        return $this->serviceProviderReferences;
    }

    /**
     * @return FacilityGroupVersion|EntityVersionInterface
     */
    public function getActiveVersion(): ?EntityVersionInterface
    {
        return $this->activeVersion;
    }

    /**
     * @param FacilityGroupVersion|EntityVersionInterface $version
     * @return FacilityGroup
     */
    public function setActiveVersion(EntityVersionInterface $version): VersionedEntityInterface
    {
        if (!($version instanceof FacilityGroupVersion)) {
            throw new RuntimeException('Incorrect entity version type for FacilityGroup');
        }

        $this->activeVersion = $version;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriory(): int
    {
        return $this->priory  ?? 0;
    }

    /**
     * @param int|null $priory
     * @return FacilityGroup
     */
    public function setPriory(?int $priory): FacilityGroup
    {
        $this->priory = $priory;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param null|string $alias
     * @return FacilityGroup
     */
    public function setAlias(?string $alias): FacilityGroup
    {
        $this->alias = $alias;
        return $this;
    }
}