<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Dictionary\ImageType;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasOrderTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\CollectionSetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\CDN\ResizableImageInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelImage
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelImageRepository")
 * @ORM\Table(name="hotels_db_hotel_image", indexes={
 *     @ORM\Index(name="IMAGE_HOTEL_ID_IDX", columns={"hotel_id"}),
 *     @ORM\Index(name="IMAGE_TYPE_ID_IDX", columns={"image_type_id"}),
 *     @ORM\Index(name="IMAGE_ORIGINAL_URL_IDX", columns={"original_url"}),
 *     @ORM\Index(name="IMAGE_RESIZED_IDX", columns={"resized"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelImage implements
    ResizableImageInterface,
    ComparableInterface,
    ServiceProviderReferencedEntityInterface,
    HasGetterMappingInterface,
    HasSetterMappingInterface
{
    use HasIntegerIdTrait,
        HasOrderTrait,
        HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    public const SIZE_SMALL = [74, null];
    public const SIZE_MEDIUM = [117, null];
    public const SIZE_NORMAL = [320, null];
    public const SIZE_BIG = [800, null];
    public const SIZE_XL = [1024, null];
    public const SIZE_XXL = [2048, null];

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelImageSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var HotelImageSPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel", inversedBy="images")
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)
     * @var Hotel|null
     */
    private $hotel;

    /**
     * @ORM\ManyToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoom")
     * @ORM\JoinTable(name="hotels_db_hotel_image_to_room",
     *     joinColumns={@ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=false)},
     * ))
     * @var HotelRoom[]|Collection
     */
    private $rooms;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\ImageType")
     * @ORM\JoinColumn(name="image_type_id", referencedColumnName="id", nullable=true)
     * @var ImageType|null
     */
    private $type;

    /**
     * @ORM\Column(name="original_url", type="string", nullable=true)
     * @var string|null
     */
    private $originalUrl;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelImageResized", mappedBy="original", fetch="LAZY", cascade={"persist", "remove"})
     * @var HotelImageResized[]|Collection
     */
    private $resizedImages;

    /**
     * @ORM\Column(name="resized", type="datetime_immutable", nullable=true)
     * @var \DateTimeImmutable
     */
    private $resized;

    /**
     * HotelImage constructor.
     */
    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
        $this->resizedImages = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }

    public static function createDefaultImage()
    {
        return new self;
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('rooms'),
            EqualComparator::attributeFactory('type'),
            new GetterAttribute('originalUrl')
        );
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new CollectionSetterAttribute('rooms'),
            new SetterAttribute('type'),
            ScalarHydrator::getStringAttribute('originalUrl')
        );
    }

    /**
     * @return HotelImageSPReference[]|Collection
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
     * @return HotelImage
     */
    public function setHotel(?Hotel $hotel): HotelImage
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return HotelRoom|null
     */
    public function getRooms(): ?Collection
    {
        return $this->rooms;
    }

    /**
     * @param HotelRoom $room
     * @return HotelImage
     */
    public function addRooms(HotelRoom $room): HotelImage
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
        }

        return $this;
    }

    /**
     * @param HotelRoom $roo
     * @return HotelImage
     */
    public function removeRooms(HotelRoom $roo): HotelImage
    {
        if ($this->rooms->contains($roo)) {
            $this->rooms->removeElement($roo);
        }

        return $this;
    }

    /**
     * @return ImageType|null
     */
    public function getType(): ?ImageType
    {
        return $this->type;
    }

    /**
     * @param ImageType|null $type
     * @return HotelImage
     */
    public function setType(?ImageType $type): HotelImage
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getOriginalUrl(): ?string
    {
        return $this->originalUrl;
    }

    /**
     * @param null|string $originalUrl
     * @return HotelImage
     */
    public function setOriginalUrl(?string $originalUrl): HotelImage
    {
        $this->originalUrl = $originalUrl;
        return $this;
    }

    /**
     * @return HotelImageSPReference[]|Collection
     */
    public function getResizedImages()
    {
        return $this->resizedImages;
    }

    /**
     * @param HotelImageResized $resized
     * @return $this
     */
    public function addResizedImages(HotelImageResized $resized): self
    {
        if (!$this->resizedImages->contains($resized)) {
            $this->resizedImages->add($resized);
            $resized->setOriginal($this);
        }

        return $this;
    }

    /**
     * @param HotelImageResized $resized
     * @return $this
     */
    public function removeResizedImages(HotelImageResized $resized): self
    {
        if ($this->resizedImages->contains($resized)) {
            $this->resizedImages->removeElement($resized);
            $resized->setOriginal(null);
        }

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getResized(): ?\DateTimeImmutable
    {
        return $this->resized;
    }

    /**
     * @param \DateTimeImmutable $resized
     * @return HotelImage
     */
    public function setResized(?\DateTimeImmutable $resized): HotelImage
    {
        $this->resized = $resized;
        return $this;
    }

    /**
     * Drop resized after update images if not set actual resized time
     *
     * @ORM\PreUpdate()
     *
     * @param PreUpdateEventArgs $eventArgs
     * @throws \Exception
     */
    public function setUpdatedOnPreUpdate(PreUpdateEventArgs $eventArgs): void
    {
        if (!$eventArgs->hasChangedField('resized')) {
            $this->setResized(null);
        }
    }

    /**
     * @inheritdoc
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof self) || \get_class($this) !== \get_class($item)) {
            throw new InvalidArgumentException('Cannot compare hotel facility value with different class');
        }

        if ($this->getOriginalUrl()) {
            return $this->getOriginalUrl() === $item->getOriginalUrl() ? 0 : ($this->orderCompare($item) ?? 1);
        }

        return $this->orderCompare($item) ?? 1;
    }

    /**
     * @inheritdoc
     */
    public function isEqual(ComparableInterface $item): bool
    {
        return $this->compare($item) === 0;
    }

    /**
     * @return HotelImageResized|null
     */
    public function getResizedImageSmall(): ?HotelImageResized
    {
        return $this->findBestResizedImage(...self::SIZE_SMALL);
    }

    /**
     * @return HotelImageResized|null
     */
    public function getResizedImageMedium(): ?HotelImageResized
    {
        return $this->findBestResizedImage(...self::SIZE_MEDIUM);
    }


    /**
     * @return HotelImageResized|null
     */
    public function getResizedImageNormal(): ?HotelImageResized
    {
        return $this->findBestResizedImage(...self::SIZE_NORMAL);
    }

    /**
     * @return HotelImageResized|null
     */
    public function getResizedImageBig(): ?HotelImageResized
    {
        return $this->findBestResizedImage(...self::SIZE_BIG);
    }

    /**
     * @return HotelImageResized|null
     */
    public function getResizedImageXL(): ?HotelImageResized
    {
        return $this->findBestResizedImage(...self::SIZE_XL);
    }

    /**
     * @return HotelImageResized|null
     */
    public function getResizedImageXXL(): ?HotelImageResized
    {
        return $this->findBestResizedImage(...self::SIZE_XXL);
    }

    /**
     * @param int $width
     * @param int|null $height
     * @return HotelImageResized|null
     */
    public function findBestResizedImage(int $width, ?int $height = null): ? HotelImageResized
    {
        $currentBestDiff = $currentBestImage = null;
        foreach ($this->resizedImages as $resizedImage) {
            $diff = abs($resizedImage->getWidth() - $width) + abs($resizedImage->getHeight() - $height);
            if ($diff === 0) {
                return $resizedImage;
            }

            if (!$currentBestDiff || $currentBestDiff > $diff) {
                $currentBestDiff = $diff;
                $currentBestImage = $resizedImage;
            }
        }

        return $currentBestImage;
    }
}