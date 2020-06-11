<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Apl\HotelsDbBundle\Entity\Geo\Bounds;
use Doctrine\ORM\Mapping as ORM;

trait HasBoundsViewportTrait
{
    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Geo\Bounds", columnPrefix="bounds_")
     * @var Bounds
     */
    private $bounds;

    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Geo\Bounds", columnPrefix="viewport_")
     * @var Bounds
     */
    private $viewport;

    /**
     * @return Bounds
     */
    public function getBounds(): ?Bounds
    {
        return $this->bounds;
    }

    /**
     * @param Bounds $bounds
     * @return $this
     */
    public function setBounds(?Bounds $bounds)
    {
        $this->bounds = $bounds;
        return $this;
    }

    /**
     * @return Bounds
     */
    public function getViewport(): ?Bounds
    {
        return $this->viewport;
    }

    /**
     * @param Bounds $viewport
     * @return $this
     */
    public function setViewport(?Bounds $viewport)
    {
        $this->viewport = $viewport;
        return $this;
    }
}