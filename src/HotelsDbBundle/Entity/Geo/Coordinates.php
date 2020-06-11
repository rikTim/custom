<?php


namespace Apl\HotelsDbBundle\Entity\Geo;


use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class Coordinates
 * @package Apl\HotelsDbBundle\Entity\Geo
 *
 * @ORM\Embeddable()
 */
class Coordinates implements ComparableInterface
{
    private const PRECISION = 5;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=5, nullable=true)
     * @Assert\Range(min="-180", max="180")
     * @var string
     */
    private $lat;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=5, nullable=true)
       @Assert\Range(min="-180", max="180")
     * @var string
     */
    private $lng;

    /**
     * Coordinates constructor.
     * @param string|null $lat
     * @param string|null $lng
     */
    public function __construct(string $lat = null, string $lng = null)
    {
        $this->setLat($lat)->setLng($lng);
    }

    /**
     * @return string|null
     */
    public function getLat(): ?string
    {
        return $this->lat;
    }

    /**
     * @param string $lat
     * @return Coordinates
     */
    public function setLat(?string $lat): Coordinates
    {
        $this->lat = $this->usePrecession($lat);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLng(): ?string
    {
        return $this->lng;
    }

    /**
     * @param string $lng
     * @return Coordinates
     */
    public function setLng(?string $lng): Coordinates
    {
        $this->lng = $this->usePrecession($lng);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof Coordinates) || get_class($this) !== get_class($item)) {
            throw new RuntimeException('Cannot compare coordinates with different class');
        }

        return \bccomp($this->getLat(), $item->getLat(), self::PRECISION) ?: \bccomp($this->getLng(), $item->getLng(), self::PRECISION);
    }

    /**
     * @inheritdoc
     */
    public function isEqual(ComparableInterface $item): bool
    {
        return $this->compare($item) === 0;
    }

    /**
     * @Assert\IsTrue()
     * @param string $coordinate
     * @return bool
     */
    private function isInRange(?string $coordinate): bool
    {
        return !($coordinate !== null && (
            \bccomp('180', $coordinate, self::PRECISION) === -1
            || \bccomp(-180, $coordinate, self::PRECISION) === 1
        ));
    }

    /**
     * @param null|string $coordinate
     * @return null|string
     */
    private function usePrecession(?string $coordinate): ?string
    {
        return $this->isInRange($coordinate) ? bcadd($coordinate, '0', self::PRECISION) : null;
    }
}