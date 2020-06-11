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
 * Class Room
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\RoomRepository")
 * @ORM\Table(name="hotels_db_dictionary_room", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),})
 * @ORM\HasLifecycleCallbacks()
 */
class Room extends AbstractRoom implements ServiceProviderReferencedEntityInterface, VersionedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\RoomSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var RoomSPReference[]|ArrayCollection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\RoomVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var RoomVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return RoomVersion::class;
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
     * @return RoomSPReference[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
        return $this->serviceProviderReferences;
    }

    /**
     * @return RoomVersion|EntityVersionInterface
     */
    public function getActiveVersion(): ?EntityVersionInterface
    {
        return $this->activeVersion;
    }

    /**
     * @param RoomVersion|EntityVersionInterface $version
     * @return Room
     */
    public function setActiveVersion(EntityVersionInterface $version): VersionedEntityInterface
    {
        if (!($version instanceof RoomVersion)) {
            throw new RuntimeException('Incorrect entity version type for Room');
        }

        $this->activeVersion = $version;
        return $this;
    }

    /**
     * @param int $adultQuantity
     * @param int $childrenQuantity
     * @return array
     */
    public function calculateMaxPax(int $adultQuantity, int $childrenQuantity): array
    {
        $maxChildren = ($this->getMaxChildren() >= 0 && $this->getMaxChildren() <= 20)
            ? $this->getMaxChildren()
            : 20;

        $maxAdults = ($this->getMaxAdults() > 0 && $this->getMaxAdults() <= 20)
            ? $this->getMaxAdults()
            : 20;

        $hasMaxPax = ($this->getMaxPax() > 0 && $this->getMaxPax() <= 20);
        $maxPax = $hasMaxPax ? $this->getMaxPax() : min($maxAdults + $maxChildren, 20);

        // Сколько нужно мест для взрослых
        $adultNeeded = min($adultQuantity, $maxAdults, $maxPax);

        // Сколько выделено "детских" мест
        // Фактически дети могут ехать по взрослой стоимости если это единственный вариант размещения
        $children = min($childrenQuantity, $maxChildren, $maxPax - $adultNeeded);

        // Если у нас указано максимальное количество мест, тогда нужно учесть детей
        $adults = $hasMaxPax ? min($maxAdults, $maxPax - $children) : $adultNeeded;

        return [
            'adults' => $adults,
            'children' => $children,
        ];
    }

}