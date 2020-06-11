<?php


namespace Apl\HotelsDbBundle\Entity;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class ServiceProviderAlias
 * @package Apl\HotelsDbBundle\Entity
 *
 * @ORM\Embeddable()
 */
class ServiceProviderAlias extends AbstractAlias
{
    /**
     * @param ComparableInterface $item
     * @return int
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof ServiceProviderAlias)) {
            throw new RuntimeException('Cannot compare alias with different class');
        }

        return strcmp($this->getAlias(), $item->getAlias());
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ComparableInterface $item) : bool
    {
        return $this->compare($item) === 0;
    }
}