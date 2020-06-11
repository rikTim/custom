<?php

namespace Apl\HotelsDbBundle\Entity\Location;

use Apl\HotelsDbBundle\Entity\AbstractAlias;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocationAlias
 * @package Apl\HotelsDbBundle\Entity\Destination
 *
 * @ORM\Embeddable()
 */
final class LocationAlias extends AbstractAlias
{
    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $alias;
}