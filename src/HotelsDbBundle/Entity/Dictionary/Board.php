<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasServiceProviderReferencesTrait;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\VersionedEntityInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectHydrator;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class Board
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\BoardRepository")
 * @ORM\Table(name="hotels_db_dictionary_board", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class Board extends AbstractBoard implements ServiceProviderReferencedEntityInterface,VersionedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\BoardSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var BoardSPReference[]|ArrayCollection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\BoardVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var BoardVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return BoardVersion::class;
    }

    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
    }

    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_RESET)
        );
    }

    /**
     * @return BoardSPReference[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
       return $this->serviceProviderReferences;
    }

    /**
     * @return BoardVersion|EntityVersionInterface
     */
    public function getActiveVersion(): ?EntityVersionInterface
    {
        return $this->activeVersion;
    }

    /**
     * @param BoardVersion|EntityVersionInterface $version
     * @return Board
     */
    public function setActiveVersion(EntityVersionInterface $version): VersionedEntityInterface
    {
        if(!($version instanceof BoardVersion)){
            throw new RuntimeException('Incorrect entity version type for Board');
        }
        $this->activeVersion = $version;
        return $this;
    }
}