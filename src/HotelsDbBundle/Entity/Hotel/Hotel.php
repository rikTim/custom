<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\Dictionary\Segment;
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
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Hotel
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelRepository")
 * @ORM\Table(name="hotels_db_hotel", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class Hotel extends AbstractHotel implements ServiceProviderReferencedEntityInterface, VersionedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var HotelSPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var HotelVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @ORM\ManyToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Board", fetch="EXTRA_LAZY", indexBy="id")
     * @ORM\JoinTable(name="hotels_db_hotel_to_board",
     *     joinColumns={@ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="board_id", referencedColumnName="id", nullable=false)},
     * )
     * @var Board[]|Collection
     */
    private $boards;

    /**
     * @ORM\ManyToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Segment")
     * @ORM\JoinTable(name="hotels_db_hotel_to_segment",
     *     joinColumns={@ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="segment_id", referencedColumnName="id", nullable=false)},
     * )
     * @var Segment[]|Collection
     */
    private $segments;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelPhone", mappedBy="hotel", cascade={"all"})
     * @var HotelPhone[]|Collection
     */
    private $phones;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelRoom", mappedBy="hotel", cascade={"all"})
     * @var HotelRoom[]|Collection
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="HotelFacility", mappedBy="hotel", cascade={"all"})
     * @var HotelFacility[]|Collection
     */
    private $facilities;

    /**
     * @ORM\OneToMany(targetEntity="HotelInterestPoint", mappedBy="hotel", cascade={"all"})
     * @var HotelInterestPoint[]|Collection
     */
    private $interestPoints;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelImage", mappedBy="hotel", cascade={"all"})
     * @var HotelImage[]|Collection
     */
    private $images;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelImage", mappedBy="hotel")
     * @var HotelImage
     */
    private $mainImage;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelTerminal", mappedBy="hotel", cascade={"all"})
     * @var HotelTerminal[]|Collection
     */
    private $terminals;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return HotelVersion::class;
    }

    /**
     * Hotel constructor.
     */
    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
        $this->boards = new ArrayCollection();
        $this->segments = new ArrayCollection();
        $this->phones = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->facilities = new ArrayCollection();
        $this->interestPoints = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->terminals = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return parent::getGetterMapping()
            ->addAttribute(new GetterAttribute('boards'))
            ->addAttribute(new GetterAttribute('segments'))
            ->addAttribute(new GetterAttribute('phones'))
            ->addAttribute(new GetterAttribute('rooms'))
            ->addAttribute(new GetterAttribute('facilities'))
            ->addAttribute(new GetterAttribute('interestPoints'))
            ->addAttribute(new GetterAttribute('images'))
            ->addAttribute(new GetterAttribute('terminals'));
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()
            ->addAttribute(CollectionMergeHydrator::attributeFactory('boards'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('segments'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('phones'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('rooms'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('facilities'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('interestPoints'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('images'))
            ->addAttribute(CollectionMergeHydrator::attributeFactory('terminals'))
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
     * @return Board[]|Collection
     */
    public function getBoards(): Collection
    {
        return $this->boards;
    }

    /**
     * @param Board $board
     * @return Hotel
     */
    public function addBoards(Board $board): Hotel
    {
        if (!$this->boards->contains($board)) {
            $this->boards->add($board);
        }

        return $this;
    }

    /**
     * @param Board $board
     * @return Hotel
     */
    public function removeBoards(Board $board): Hotel
    {
        if ($this->boards->contains($board)) {
            $this->boards->removeElement($board);
        }

        return $this;
    }

    /**
     * @return Segment[]|Collection
     */
    public function getSegments(): Collection
    {
        return $this->segments;
    }

    /**
     * @param Segment $segment
     * @return Hotel
     */
    public function addSegments(Segment $segment): Hotel
    {
        if (!$this->segments->contains($segment)) {
            $this->segments->add($segment);
        }

        return $this;
    }

    /**
     * @param Segment $segment
     * @return Hotel
     */
    public function removeSegments(Segment $segment): Hotel
    {
        if ($this->segments->contains($segment)) {
            $this->segments->removeElement($segment);
        }

        return $this;
    }

    /**
     * @return HotelPhone[]|Collection
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    /**
     * @param HotelPhone $phone
     * @return Hotel
     */
    public function addPhones(HotelPhone $phone): Hotel
    {
        if (!$this->phones->contains($phone)) {
            $this->phones->add($phone);
            $phone->setHotel($this);
        }

        return $this;
    }

    /**
     * @param HotelPhone $phone
     * @return Hotel
     */
    public function removePhones(HotelPhone $phone): Hotel
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
            $phone->setHotel(null);
        }

        return $this;
    }

    /**
     * @return HotelRoom[]|Collection
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    /**
     * @param HotelRoom $hotelRoom
     * @return Hotel
     */
    public function addRooms(HotelRoom $hotelRoom): Hotel
    {
        if (!$this->rooms->contains($hotelRoom)) {
            $this->rooms->add($hotelRoom);
            $hotelRoom->setHotel($this);
        }

        return $this;
    }

    /**
     * @param HotelRoom $hotelRoom
     * @return Hotel
     */
    public function removeRooms(HotelRoom $hotelRoom): Hotel
    {
        if ($this->rooms->contains($hotelRoom)) {
            $this->rooms->removeElement($hotelRoom);
            $hotelRoom->setHotel(null);
        }

        return $this;
    }

    /**
     * @return HotelFacility[]|Collection
     */
    public function getFacilities(): Collection
    {
        return $this->facilities;
    }

    /**
     * @param HotelFacility $facility
     * @return Hotel
     */
    public function addFacilities(HotelFacility $facility): Hotel
    {
        if (!$this->facilities->contains($facility)) {
            $this->facilities->add($facility);
            $facility->setHotel($this);
        }

        return $this;
    }

    /**
     * @param HotelFacility $facility
     * @return Hotel
     */
    public function removeFacilities(HotelFacility $facility): Hotel
    {
        if ($this->facilities->contains($facility)) {
            $this->facilities->removeElement($facility);
            $facility->setHotel(null);
        }

        return $this;
    }

    /**
     * @return HotelInterestPoint[]|Collection
     */
    public function getInterestPoints(): Collection
    {
        return $this->interestPoints;
    }

    /**
     * @param HotelInterestPoint $interestPoint
     * @return Hotel
     */
    public function addInterestPoints(HotelInterestPoint $interestPoint): Hotel
    {
        if (!$this->interestPoints->contains($interestPoint)) {
            $this->interestPoints->add($interestPoint);
            $interestPoint->setHotel($this);
        }

        return $this;
    }

    /**
     * @param HotelInterestPoint $interestPoint
     * @return Hotel
     */
    public function removeInterestPoint(HotelInterestPoint $interestPoint): Hotel
    {
        if ($this->interestPoints->contains($interestPoint)) {
            $this->interestPoints->removeElement($interestPoint);
            $interestPoint->setHotel(null);
        }

        return $this;
    }
    /**
     * @return HotelImage[]|Collection
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @param HotelImage $image
     * @return Hotel
     */
    public function addImages(HotelImage $image): Hotel
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setHotel($this);
        }

        return $this;
    }

    /**
     * @param HotelFacility $image
     * @return Hotel
     */
    public function removeImages(HotelFacility $image): Hotel
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            $image->setHotel(null);
        }

        return $this;
    }

    /**
     * @return HotelImage
     */
    public function getMainImage(): HotelImage
    {
        if (!$this->mainImage && $this->images->count() === 0) {
            $this->mainImage = HotelImage::createDefaultImage();
        }

        return $this->mainImage;
    }

    /**
     * @return HotelTerminal[]|Collection
     */
    public function getTerminals(): Collection
    {
        return $this->terminals;
    }

    /**
     * @param HotelTerminal $terminal
     * @return Hotel
     */
    public function addTerminals(HotelTerminal $terminal): Hotel
    {
        if (!$this->terminals->contains($terminal)) {
            $this->terminals->add($terminal);
            $terminal->setHotel($this);
        }

        return $this;
    }

    /**
     * @param HotelTerminal $terminal
     * @return Hotel
     */
    public function removeTerminal(HotelTerminal $terminal): Hotel
    {
        if ($this->terminals->contains($terminal)) {
            $this->terminals->removeElement($terminal);
            $terminal->setHotel(null);
        }

        return $this;
    }
}