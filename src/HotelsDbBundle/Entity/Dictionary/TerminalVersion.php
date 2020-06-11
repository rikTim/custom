<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;

use Apl\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TerminalVersion
 * @package Apl\HotelsDbBundle\Entity\Destination
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\TerminalVersionRepository")
 * @ORM\Table(name="hotels_db_dictionary_terminal_version", indexes={
 *     @ORM\Index(name="TERMINAL_ID_IDX", columns={"terminal_id"})
 * })
 */
class TerminalVersion extends AbstractTerminal implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Terminal", cascade={"persist"})
     * @ORM\JoinColumn(name="terminal_id", referencedColumnName="id", nullable=false)
     * @var Terminal
     */
    protected $entity;

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_MERGE)
        );
    }
}