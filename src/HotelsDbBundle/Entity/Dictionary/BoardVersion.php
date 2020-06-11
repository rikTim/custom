<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Traits\EntityVersionTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BoardVersion
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 *@ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\BoardVersionRepository")
 * @ORM\Table(name="hotels_db_dictionary_board_version", indexes={
 *     @ORM\Index(name="BOARD_ID_IDX", columns={"board_id"})
 * })
 */
class BoardVersion extends AbstractBoard implements EntityVersionInterface
{
    use EntityVersionTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Board", cascade={"persist"})
     * @ORM\JoinColumn(name="board_id", referencedColumnName="id", nullable=false)
     * @var Board
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