<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Apl\HotelsDbBundle\Entity\Geo\Coordinates;
use Doctrine\ORM\Mapping as ORM;


trait HasCoordinatesTrait
{
    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Geo\Coordinates", columnPrefix=false)
     * @var Coordinates
     */
    private $coordinates;

    /**
     * @return Coordinates
     */
    public function getCoordinates(): ?Coordinates
    {
        return $this->coordinates;
    }

    /**
     * @param Coordinates $coordinates
     * @return HasCoordinatesTrait
     */
    public function setCoordinates(?Coordinates $coordinates)
    {
        $this->coordinates = $coordinates;
        return $this;
    }
}