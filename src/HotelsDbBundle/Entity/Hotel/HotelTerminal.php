<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Dictionary\Terminal;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelTerminal
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelTerminalRepository")
 * @ORM\Table(name="hotels_db_hotel_terminal", indexes={
 *     @ORM\Index(name="TERMINAL_HOTEL_ID_IDX", columns={"hotel_id"}),
 *     @ORM\Index(name="TERMINAL_TERMINAL_ID_IDX", columns={"terminal_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelTerminal implements ServiceProviderReferencedEntityInterface, ComparableInterface, HasSetterMappingInterface
{
    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelTerminalSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var HotelTerminalSPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel", inversedBy="terminals")
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)
     * @var Hotel|null
     */
    private $hotel;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Terminal")
     * @ORM\JoinColumn(name="terminal_id", referencedColumnName="id", nullable=false)
     * @var Terminal|null
     */
    private $terminal;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     * @var integer
     */
    private $distance;

    /**
     * HotelTerminal constructor.
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
        return new SetterMapping(
            new SetterAttribute('terminal'),
            ScalarHydrator::getIntegerAttribute('distance')
        );
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
     * @return HotelTerminal
     */
    public function setHotel(?Hotel $hotel): HotelTerminal
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return Terminal|null
     */
    public function getTerminal(): ?Terminal
    {
        return $this->terminal;
    }

    /**
     * @param Terminal|null $terminal
     * @return HotelTerminal
     */
    public function setTerminal(?Terminal $terminal): HotelTerminal
    {
        $this->terminal = $terminal;
        return $this;
    }

    /**
     * @return int
     */
    public function getDistance(): int
    {
        return $this->distance;
    }

    /**
     * @param int $distance
     * @return HotelTerminal
     */
    public function setDistance(int $distance): HotelTerminal
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof self) || \get_class($this) !== \get_class($item)) {
            throw new InvalidArgumentException('Cannot compare hotel terminal with different class');
        }

        if (!$item->getTerminal()) {
            return 1;
        }

        if (!$this->getTerminal()) {
            return -1;
        }

        return $this->getTerminal()->getIsoCode() === $item->getTerminal()->getIsoCode()
            ? 0
            : ($this->getDistance() - $item->getDistance() ?? 1);
    }

    /**
     * @inheritdoc
     */
    public function isEqual(ComparableInterface $item): bool
    {
        return $this->compare($item) === 0;
    }
}