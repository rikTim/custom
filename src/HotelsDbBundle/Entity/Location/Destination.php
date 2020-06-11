<?php


namespace Apl\HotelsDbBundle\Entity\Location;


use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\CollectionMergeHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\VersionedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Destination
 * @package Apl\HotelsDbBundle\Entity\Destination
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Location\DestinationRepository")
 * @ORM\Table(name="hotels_db_location_destination", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class Destination extends AbstractDestination implements ServiceProviderReferencedEntityInterface, VersionedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Location\DestinationSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @var DestinationSPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Location\DestinationVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var DestinationVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @ORM\ManyToMany(targetEntity="Apl\HotelsDbBundle\Entity\Location\Destination", inversedBy="containedInTheDestinations", cascade={"persist"})
     * @ORM\JoinTable(name="hotels_db_location_destination_relations",
     *     joinColumns={@ORM\JoinColumn(name="destination_id", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="related_id", referencedColumnName="id", nullable=false)},
     *     )
     * @var Destination[]|Collection
     */
    private $containsDestinations;

    /**
     * @ORM\ManyToMany(targetEntity="Apl\HotelsDbBundle\Entity\Location\Destination", mappedBy="containsDestinations", cascade={"persist"})
     * @var Destination[]|Collection
     */
    private $containedInTheDestinations;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return DestinationVersion::class;
    }

    /**
     * Destination constructor.
     */
    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
        $this->containsDestinations = new ArrayCollection();
        $this->containedInTheDestinations = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return parent::getGetterMapping()
            ->addAttribute(new GetterAttribute('containsDestinations'))
            ->addAttribute(new GetterAttribute('containedInTheDestinations'));
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()
            ->addAttribute(CollectionMergeHydrator::attributeFactory('containsDestinations'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('containedInTheDestinations'))
            ->addAttribute(TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_RESET));
    }

    /**
     * @return ServiceProviderReferenceInterface[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
        return $this->serviceProviderReferences;
    }

    /**
     * @return EntityVersionInterface|null
     */
    public function getActiveVersion(): ?EntityVersionInterface
    {
        return $this->activeVersion;
    }

    /**
     * @inheritdoc
     */
    public function setActiveVersion(EntityVersionInterface $version): VersionedEntityInterface
    {
        $versionClassName = $this::getVersionClassName();
        if (!($version instanceof $versionClassName)) {
            throw new InvalidArgumentException(sprintf('Incorrect entity version type for "%s"', \get_class($this)));
        }

        $this->activeVersion = $version;
        return $this;
    }

    /**
     * @return Destination[]|Collection
     */
    public function getContainsDestinations(): Collection
    {
        return $this->containsDestinations;
    }

    /**
     * @param Destination
     * @return Destination
     */
    public function addContainsDestinations(Destination $destination): Destination
    {
        if (!$this->containsDestinations->contains($destination)) {
            $this->containsDestinations->add($destination);
            $destination->addContainedInTheDestinations($this);
        }

        return $this;
    }

    /**
     * @param Destination $destination
     * @return Destination
     */
    public function removeContainsDestinations(Destination $destination): Destination
    {
        if ($this->containsDestinations->contains($destination)) {
            $this->containsDestinations->removeElement($destination);
            $destination->removeContainedInTheDestinations($this);
        }

        return $this;
    }

    /**
     * @return Destination[]|Collection
     */
    public function getContainedInTheDestinations(): Collection
    {
        return $this->containedInTheDestinations;
    }

    /**
     * @param Destination
     * @return Destination
     */
    public function addContainedInTheDestinations(Destination $destination): Destination
    {
        if (!$this->containedInTheDestinations->contains($destination)) {
            $this->containedInTheDestinations->add($destination);
            $destination->addContainsDestinations($this);
        }

        return $this;
    }

    /**
     * @param Destination $destination
     * @return Destination
     */
    public function removeContainedInTheDestinations(Destination $destination): Destination
    {
        if ($this->containedInTheDestinations->contains($destination)) {
            $this->containedInTheDestinations->removeElement($destination);
            $destination->removeContainsDestinations($this);
        }

        return $this;
    }
}