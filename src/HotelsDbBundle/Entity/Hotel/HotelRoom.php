<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Location\DestinationSPReference;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\VersionedEntityInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\CollectionMergeHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class HotelRoom
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelRoomRepository")
 * @ORM\Table(name="hotels_db_hotel_room", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),
 * }, indexes={
 *     @ORM\Index(name="ROOM_HOTEL_ID_IDX", columns={"hotel_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()

 */
class HotelRoom extends AbstractHotelRoom implements ServiceProviderReferencedEntityInterface, VersionedEntityInterface, \JsonSerializable
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoomSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @var HotelRoomSPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoomVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var HotelRoomVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel", inversedBy="rooms", cascade={"persist", "refresh"})
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)
     * @var Hotel|null
     */
    private $hotel;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoomFacility", mappedBy="room", cascade={"all"})
     * @var HotelRoomFacility[]|Collection
     * @Groups({"public"})
     */
    private $facilities;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoomStay", mappedBy="room", cascade={"all"})
     * @var HotelRoomStay[]|Collection
     * @Groups({"public"})
     */
    private $stays;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return HotelRoomVersion::class;
    }

    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
        $this->facilities = new ArrayCollection();
        $this->stays = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return parent::getGetterMapping()
            ->addAttribute(new GetterAttribute('facilities'))
            ->addAttribute(new GetterAttribute('stays'));
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()
            ->addAttribute(CollectionMergeHydrator::attributeFactory('facilities'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('stays'))
            ->addAttribute(TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_RESET));
    }

    /**
     * @return DestinationSPReference[]|Collection
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
     * @return Hotel|null
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel|null $hotel
     * @return HotelRoom
     */
    public function setHotel(?Hotel $hotel): HotelRoom
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return HotelRoomFacility[]|Collection
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * @param HotelRoomFacility $facility
     * @return HotelRoom
     */
    public function addFacilities(HotelRoomFacility $facility): HotelRoom
    {
        if (!$this->facilities->contains($facility)) {
            $this->facilities->add($facility);
            $facility->setRoom($this);
        }

        return $this;
    }

    /**
     * @param HotelRoomFacility $facility
     * @return HotelRoom
     */
    public function removeFacilities(HotelRoomFacility $facility): HotelRoom
    {
        if ($this->facilities->contains($facility)) {
            $this->facilities->removeElement($facility);
            $facility->setRoom(null);
        }

        return $this;
    }

    /**
     * @return HotelRoomStay[]|Collection
     */
    public function getStays()
    {
        return $this->stays;
    }

    /**
     * @param HotelRoomStay $stay
     * @return HotelRoom
     */
    public function addStays(HotelRoomStay $stay): HotelRoom
    {
        if (!$this->stays->contains($stay)) {
            $this->stays->add($stay);
            $stay->setRoom($this);
        }

        return $this;
    }

    /**
     * @param HotelRoomStay $stay
     * @return HotelRoom
     */
    public function removeStays(HotelRoomStay $stay): HotelRoom
    {
        if ($this->stays->contains($stay)) {
            $this->stays->removeElement($stay);
            $stay->setRoom(null);
        }

        return $this;
    }


    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'facilities' => $this->getFacilities(),
            'type' => $this->getType()
        ];
    }
}