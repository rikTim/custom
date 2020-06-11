<?php

namespace Apl\HotelsDbBundle\Entity;


use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractAlias
 * @package Apl\HotelsDbBundle\Entity
 *
 * @ORM\MappedSuperclass()
 */
abstract class AbstractAlias implements ComparableInterface
{
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string|null
     */
    protected $alias;

    /**
     * AbstractAlias constructor.
     * @param string $alias
     */
    public function __construct(?string $alias)
    {
        $this->alias = $alias ? mb_strtolower($alias) : null;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->getAlias();
    }

    /**
     * @return string|null
     */
    public function getAlias() : ?string
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof AbstractAlias) || \get_class($this) !== \get_class($item)) {
            throw new RuntimeException('Cannot compare alias with different class');
        }

        return strcmp($this->getAlias(), $item->getAlias());
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ComparableInterface $item) : bool
    {
        return $item instanceof AbstractAlias
            && \get_class($this) === \get_class($item)
            && $this->getAlias() === $item->getAlias();
    }
}