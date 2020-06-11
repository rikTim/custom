<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BoardSPReference
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_dictionary_board_sp_reference")
 */
class BoardSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Board", inversedBy="serviceProviderReferences")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=true)
     * @var Board
     */
    protected $entity;
}