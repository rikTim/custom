<?php


namespace Apl\HotelsDbBundle\Entity\Geo;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class Bounds
 * @package Apl\HotelsDbBundle\Entity\Geo
 *
 * @ORM\Embeddable()
 */
class Bounds implements ComparableInterface
{
    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Geo\Coordinates", columnPrefix="sw_")
     * @var Coordinates
     */
    private $southwest;

    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Geo\Coordinates", columnPrefix="ne_")
     * @var Coordinates
     */
    private $northeast;

    public function __construct(Coordinates $southwest = null, Coordinates $northeast = null)
    {
        $this->setSouthwest($southwest)->setNortheast($northeast);
    }

    /**
     * @return Coordinates
     */
    public function getSouthwest(): Coordinates
    {
        if (!$this->southwest) {
            $this->setSouthwest(new Coordinates());
        }

        return $this->southwest;
    }

    /**
     * @param Coordinates $southwest
     * @return Bounds
     */
    public function setSouthwest(?Coordinates $southwest): Bounds
    {
        $this->southwest = $southwest;
        return $this;
    }

    /**
     * @return Coordinates
     */
    public function getNortheast(): Coordinates
    {
        if (!$this->northeast) {
            $this->setNortheast(new Coordinates());
        }

        return $this->northeast;
    }

    /**
     * @param Coordinates $northeast
     * @return Bounds
     */
    public function setNortheast(?Coordinates $northeast): Bounds
    {
        $this->northeast = $northeast;
        return $this;
    }

    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof Bounds) || get_class($this) !== get_class($item)) {
            throw new RuntimeException('Cannot compare bounds with different class');
        }

        return $this->getSouthwest()->compare($item->getSouthwest()) ?: $this->getNortheast()->compare($item->getNortheast());
    }

    public function isEqual(ComparableInterface $item): bool
    {
        return $this->compare($item) === 0;
    }
}