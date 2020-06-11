<?php


namespace Apl\HotelsDbBundle\Entity\SearchIndex;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Service\Search\SearchIndexEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocationSearchIndex
 *
 * @package Apl\HotelsDbBundle\Entity\SearchIndex
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\SearchIndex\LocationSearchIndexRepository")
 * @ORM\Table(name="hotels_db_search_location_index", indexes={
 *      @ORM\Index(name="LOCATIONS_FULLTEXT_INDEX", columns={"index_data"}, flags={"fulltext"}),
 * }, uniqueConstraints={
 *      @ORM\UniqueConstraint(name="ENTITY_UNIQUE", columns={"entity_id", "entity_class_name", "locale"}),
 * })
 */
class LocationSearchIndex implements SearchIndexEntityInterface
{
    use HasIntegerIdTrait;

    /**
     * @ORM\Column(name="index_data", type="string", length=255)
     * @var string
     */
    private $indexData;

    /**
     * @ORM\Column(type="float", options={"default" = 1})
     * @var int
     */
    private $score = 1;

    /**
     * @ORM\Column(name="entity_class_name", type="string", nullable=false, length=255)
     * @var string
     */
    private $entityClassName;

    /**
     * @ORM\Column(name="entity_id", type="integer", nullable=false, options={"unsigned"=true})
     * @var int
     */
    private $entityId;

    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Locale", columnPrefix=false)
     * @var Locale
     */
    private $locale;

    /**
     * @return string
     */
    public function getIndexData(): string
    {
        return $this->indexData;
    }

    /**
     * @param string $indexData
     * @return LocationSearchIndex
     */
    public function setIndexData(string $indexData): LocationSearchIndex
    {
        $this->indexData = $indexData;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * @param string $entityClassName
     * @return LocationSearchIndex
     */
    public function setEntityClassName(string $entityClassName): LocationSearchIndex
    {
        $this->entityClassName = $entityClassName;
        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     * @return LocationSearchIndex
     */
    public function setEntityId(int $entityId): LocationSearchIndex
    {
        $this->entityId = $entityId;
        return $this;
    }
}