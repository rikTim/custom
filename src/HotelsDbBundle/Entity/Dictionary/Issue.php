<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;

use Apl\HotelsDbBundle\Entity\AbstractAlias;
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
 * Class Issue
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Dictionary\IssueRepository")
 * @ORM\Table(name="hotels_db_dictionary_issue", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVE_VERSION_UNIQUE", columns={"active_version_id"}),})
 * @ORM\HasLifecycleCallbacks()
 */
class Issue extends AbstractIssue implements ServiceProviderReferencedEntityInterface,VersionedEntityInterface
{
    use HasDateTimeUpdatedTrait,
        HasServiceProviderReferencesTrait;

    /**
     * @ORM\OneToMany(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\IssueSPReference", mappedBy="entity", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var IssueSPReference[]|ArrayCollection
     */
    private $serviceProviderReferences;

    /**
     * @ORM\OneToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\IssueVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="active_version_id", nullable=true)
     * @var IssueVersion|EntityVersionInterface
     */
    private $activeVersion;

    /**
     * @return string
     */
    public static function getVersionClassName(): string
    {
        return IssueVersion::class;
    }

    public function __construct()
    {
        $this->serviceProviderReferences = new ArrayCollection();
    }

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(
            TranslatableObjectHydrator::attributeFactory(TranslatableObjectHydrator::OPTION_STRATEGY_RESET)
        );
    }

    /**
     * @return IssueSPReference[]|Collection
     */
    public function getServiceProviderReferences(): Collection
    {
        return $this->serviceProviderReferences;
    }

    /**
     * @return IssueVersion|EntityVersionInterface
     */
    public function getActiveVersion(): ?EntityVersionInterface
    {
        return $this->activeVersion;
    }

    /**
     * @param IssueVersion|EntityVersionInterface $version
     * @return Issue
     */
    public function setActiveVersion(EntityVersionInterface $version): VersionedEntityInterface
    {
        if (!($version instanceof IssueVersion)) {
            throw new RuntimeException('Incorrect entity version type for Issue');
        }

        $this->activeVersion = $version;
        return $this;
    }

}