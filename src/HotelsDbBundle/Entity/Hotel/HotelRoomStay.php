<?php


namespace Base\HotelsDbBundle\Entity\Hotel;


use Base\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Base\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Base\HotelsDbBundle\Exception\InvalidArgumentException;
use Base\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Base\HotelsDbBundle\Service\EntityVersion\VersionedEntityInterface;
use Base\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Base\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Base\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelRoomStay
 * @package Base\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Base\HotelsDbBundle\Repository\Hotel\HotelRoomStayRepository")
 * @ORM\Table(name="hotels_db_hotel_room_stay", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),
 * }, indexes={
 *     @ORM\Index(name="STAY_ROOM_ID_IDX", columns={"room_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelRoomStay extends AbstractHotelRoomStay implements VersionedEntityInterface, ServiceProviderReferencedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoomStaySPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var HotelRoomStaySPReference[]|ArrayCollection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoomStayVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var HotelRoomStayVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @ORM\ManyToOne(targetEntity="HotelRoom", inversedBy="stays")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=false)
     * @var HotelRoom|null
     */
    private $room;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return HotelRoomStayVersion::class;
    }

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
     * @return HotelRoom|null
     */
    public function getRoom(): ?HotelRoom
    {
        return $this->room;
    }

    /**
     * @param HotelRoom|null $room
     * @return HotelRoomStay
     */
    public function setRoom(?HotelRoom $room): HotelRoomStay
    {
        $this->room = $room;
        return $this;
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
}