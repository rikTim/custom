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
 * Class Segment
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\SegmentRepository")
 * @ORM\Table(name="hotels_db_dictionary_segment", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),
 *     })
 * @ORM\HasLifecycleCallbacks()
 */
class Segment extends AbstractSegment implements  ServiceProviderReferencedEntityInterface, VersionedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\SegmentSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var SegmentSPReference[]|ArrayCollection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\SegmentVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var SegmentVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return SegmentVersion::class;
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
     * @return SegmentSPReference[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
       return $this->serviceProviderReferences;
    }

    /**
     * @return SegmentVersion|EntityVersionInterface
     */
    public function getActiveVersion(): ?EntityVersionInterface
    {
        return $this->activeVersion;
    }

    /**
     * @param SegmentVersion|EntityVersionInterface $version
     * @return Segment
     */
    public function setActiveVersion(EntityVersionInterface $version): VersionedEntityInterface
    {
        if (!($version instanceof SegmentVersion)) {
            throw new RuntimeException('Incorrect entity version type for Segment');
        }

        $this->activeVersion = $version;
        return $this;
    }
}