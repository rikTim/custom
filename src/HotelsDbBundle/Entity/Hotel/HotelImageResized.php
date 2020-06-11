<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\CDNAlias;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Service\CDN\ResizedImageInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HotelImageResized
 *
 * @package Apl\HotelsDbBundle\Entity\Hotel
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_hotel_image_resized", indexes={
 *     @ORM\Index(name="IMAGE_RESIZED_IMAGE_ID_IDX", columns={"original_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class HotelImageResized implements ResizedImageInterface, HasSetterMappingInterface
{
    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\HotelImage", inversedBy="resizedImages", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     * @var HotelImage
     */
    private $original;

    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\CDNAlias", columnPrefix="cdn_")
     * @var CDNAlias
     */
    private $cdnAlias;

    /**
     * @ORM\Column(name="url", type="string", nullable=false)
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(name="width", type="integer", nullable=false)
     * @var int
     */
    private $width;

    /**
     * @ORM\Column(name="height", type="integer", nullable=true)
     * @var int|null
     */
    private $height;

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            EqualComparator::attributeFactory('original'),
            EqualComparator::attributeFactory('cdnAlias'),
            new GetterAttribute('url'),
            new GetterAttribute('width'),
            new GetterAttribute('height')
        );
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new SetterAttribute('original'),
            new SetterAttribute('cdnAlias'),
            ScalarHydrator::getStringAttribute('url'),
            ScalarHydrator::getIntegerAttribute('width'),
            ScalarHydrator::getIntegerAttribute('height')
        );
    }

    /**
     * @return HotelImage
     */
    public function getOriginal(): HotelImage
    {
        return $this->original;
    }

    /**
     * @param HotelImage $original
     * @return HotelImageResized
     */
    public function setOriginal(?HotelImage $original): HotelImageResized
    {
        $this->original = $original;
        return $this;
    }

    /**
     * @return CDNAlias
     */
    public function getCdnAlias(): CDNAlias
    {
        return $this->cdnAlias;
    }

    /**
     * @param CDNAlias $cdnAlias
     * @return HotelImageResized
     */
    public function setCdnAlias(CDNAlias $cdnAlias): HotelImageResized
    {
        $this->cdnAlias = $cdnAlias;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return HotelImageResized
     */
    public function setUrl(string $url): HotelImageResized
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return HotelImageResized
     */
    public function setWidth(?int $width): HotelImageResized
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return HotelImageResized
     */
    public function setHeight(?int $height): HotelImageResized
    {
        $this->height = $height;
        return $this;
    }
}