<?php


namespace Apl\HotelsDbBundle\Entity;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class ServiceProviderAlias
 * @package Apl\HotelsDbBundle\Entity
 *
 * @ORM\Embeddable()
 */
final class NullableServiceProviderAlias extends ServiceProviderAlias
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null
     */
    public $alias;
}