<?php

namespace Base\HotelsDbBundle\Entity\Dictionary;


use Base\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChainSPReference
 * @package Base\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_dictionary_chain_sp_reference")
 */
class ChainSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Base\HotelsDbBundle\Entity\Dictionary\Chain", inversedBy="serviceProviderReferences")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var Chain
     */
    protected $entity;
}