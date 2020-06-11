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
 * Class Accommodation
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\AccommodationRepository")
 * @ORM\Table(name="hotels_db_dictionary_accommodation", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class Accommodation extends AbstractAccommodation implements ServiceProviderReferencedEntityInterface,VersionedEntityInterface
{

    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\AccommodationSpReference" ,mappedBy="entity" ,fetch="EXTRA_LAZY" ,cascade={"persist"})
     * @var AccommodationSpReference[]|ArrayCollection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\AccommodationVersion" , fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id" , nullable=true)
     * @var AccommodationVersion[]|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return AccommodationVersion::class;
    }

    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
    }

    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_RESET)
        );
    }

    /**
     * @return AccommodationSpReference[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
        return $this->serviceProviderReferences;
    }

    /**
     * @return AccommodationVersion|EntityVersionInterface|null
     */
    public function getActiveVersion(): ?EntityVersionInterface
    {
      return $this->activeVersion;
    }

    /**
     * @param AccommodationVersion[]|EntityVersionInterface $version
     * @return VersionedEntityInterface
     */
    public function setActiveVersion(EntityVersionInterface $version): VersionedEntityInterface
    {
        if(!($version instanceof AccommodationVersion)) {
            throw  new RuntimeException('Incorrect entity version type of Accommodation');
        }

        $this->activeVersion = $version;
        return $this;
    }
}