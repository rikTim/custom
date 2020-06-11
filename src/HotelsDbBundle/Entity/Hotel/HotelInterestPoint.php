<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\VersionedEntityInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class HotelInterestPoint
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelInterestPointRepository")
 * @ORM\Table(name="hotels_db_hotel_interest_point", indexes={
 *     @ORM\Index(name="INTEREST_POINT_HOTEL_ID_IDX", columns={"hotel_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelInterestPoint extends AbstractHotelInterestPoint implements VersionedEntityInterface, ServiceProviderReferencedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelInterestPointSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var HotelInterestPointSPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelInterestPointVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var HotelInterestPointVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel", inversedBy="interestPoints")
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)
     * @var Hotel|null
     */
    private $hotel;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return HotelInterestPointVersion::class;
    }

    /**
     * HotelInterestPoint constructor.
     */
    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_RESET)
        );
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
     * @return ServiceProviderReferenceInterface[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
        return $this->serviceProviderReferences;
    }

    /**
     * @return Hotel|null
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel|null $hotel
     * @return HotelInterestPoint
     */
    public function setHotel(?Hotel $hotel): HotelInterestPoint
    {
        $this->hotel = $hotel;
        return $this;
    }
}