<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelPhone
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Hotel\HotelPhoneRepository")
 * @ORM\Table(name="hotels_db_hotel_phone", indexes={
 *     @ORM\Index(name="PHONE_HOTEL_ID_IDX", columns={"hotel_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelPhone implements ServiceProviderReferencedEntityInterface, HasGetterMappingInterface, HasSetterMappingInterface
{
    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelPhoneSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var HotelPhoneSPReference[]|Collection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel", inversedBy="phones")
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)
     * @var Hotel|null
     */
    private $hotel;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string|null
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $type;

    /**
     * HotelPhone constructor.
     */
    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "{$this->getType()} {$this->getPhone()}";
    }

    public function getSearchName(): string
    {
        return "{$this->getHotel()} / {$this}";
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('phone'),
            new GetterAttribute('type')
        );
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            ScalarHydrator::getStringAttribute('phone'),
            ScalarHydrator::getStringAttribute('type')
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
     * @return HotelPhone
     */
    public function setHotel(?Hotel $hotel): HotelPhone
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     * @return HotelPhone
     */
    public function setPhone(?string $phone): HotelPhone
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param null|string $type
     * @return HotelPhone
     */
    public function setType(?string $type): HotelPhone
    {
        $this->type = $type;
        return $this;
    }
}