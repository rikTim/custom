<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Apl\HotelsDbBundle\Entity\Location\LocationAlias;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait HasLocationAliasTrait
 *
 * @package Apl\HotelsDbBundle\Entity\Traits
 */
trait HasLocationAliasTrait
{
    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Location\LocationAlias", columnPrefix=false)
     * @var LocationAlias
     */
    private $alias;

    /**
     * @return LocationAlias
     */
    public function getAlias(): ?LocationAlias
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function setAlias(?string $alias = null)
    {
        $this->alias = new LocationAlias($alias);
        return $this;
    }
}