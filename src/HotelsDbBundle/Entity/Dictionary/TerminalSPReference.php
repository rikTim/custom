<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TerminalSPReference
 * @package Apl\HotelsDbBundle\Entity\Destination
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_dictionary_terminal_sp_reference")
 */
class TerminalSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Terminal", inversedBy="serviceProviderReferences")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var Terminal
     */
    protected $entity;
}