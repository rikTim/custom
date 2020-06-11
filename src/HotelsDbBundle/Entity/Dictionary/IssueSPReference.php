<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;

use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class IssueSPReference
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_dictionary_issue_sp_reference")
 */
class IssueSPReference extends AbstractServiceProviderReference
{
    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Issue", inversedBy="serviceProviderReferences")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * @var Issue
     */
    protected $entity;
}