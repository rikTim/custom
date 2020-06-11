<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;

use Apl\HotelsDbBundle\Entity\AbstractServiceProviderReference;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CurrencySPReference
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity()
 * @ORM\Table(name="hotels_db_dictionary_currency_sp_reference")
 */
class CurrencySPReference extends AbstractServiceProviderReference
{

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Currency", inversedBy="serviceProviderReferences")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="iso_code", nullable=false)
     * @var Currency
     */
    protected $entity;
}