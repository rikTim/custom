<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Apl\HotelsDbBundle\Exception\InvalidArgumentException;

/**
 * Trait HasOrderTrait
 * @package Apl\HotelsDbBundle\Entity\Traits
 */
trait HasOrderTrait
{
    /**
     * @ORM\Column(name="`order`", type="integer", nullable=true)
     * @var int|null
     */
    private $order;

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     * @return $this
     */
    public function setOrder(?int $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param object $item
     * @return int
     * @throws \Apl\HotelsDbBundle\Exception\InvalidArgumentException
     */
    public function orderCompare($item): int
    {
        if (!\is_object($item) || !\method_exists($item, 'getOrder')) {
            throw new InvalidArgumentException('Cannot compare orders with object without getOrder methods');
        }

        return $this->getOrder() ? min(1, max(-1, $this->getOrder() - $item->getOrder())) : 1;
    }
}