<?php


namespace Apl\HotelsDbBundle\Entity\TranslateType;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Exception\LogicException;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractTranslateType
 * @package Apl\HotelsDbBundle\Entity\Translate
 *
 * @ORM\MappedSuperclass()
 * @ORM\Table(uniqueConstraints={
 *      @ORM\UniqueConstraint(name="TRANSLATABLE_UNIQUE", columns={"entity_alias", "entity_id", "locale", "entity_field"})
 * }, indexes={
 *      @ORM\Index(name="TRANSLATE_LOCALE_IDX", columns={"locale"}),
 *      @ORM\Index(name="TRANSLATE_SEARCH_CONDITION", columns={"entity_alias", "entity_field", "locale"}),
 * })
 * @UniqueEntity(fields={"locale", "entityAlias", "entityId", "entityField"})
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractTranslateType implements TranslateTypeInterface
{
    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait;

    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Locale", columnPrefix=false)
     * @var Locale
     * @Groups({"translate"})
     */
    protected $locale;

    /**
     * @ORM\Column(name="entity_alias", type="string", nullable=false, length=255)
     * @var string
     */
    private $entityAlias;

    /**
     * @ORM\Column(name="entity_id", type="integer", nullable=false, options={"unsigned"=true})
     * @var int
     */
    private $entityId;

    /**
     * @ORM\Column(name="entity_field", type="string", nullable=false, length=255)
     * @var string
     */
    private $entityField;

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @param Locale $locale
     * @return AbstractTranslateType
     */
    public function setLocale(Locale $locale): TranslateTypeInterface
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityAlias(): string
    {
        return $this->entityAlias;
    }

    /**
     * @param string $entityAlias
     * @return AbstractTranslateType
     */
    public function setEntityAlias(string $entityAlias): TranslateTypeInterface
    {
        if ($this->id) {
            throw new LogicException('Cannot change translate relation');
        }
        $this->entityAlias = $entityAlias;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     * @return AbstractTranslateType
     */
    public function setEntityId(int $entityId): TranslateTypeInterface
    {
        if ($this->id) {
            throw new LogicException('Cannot change translate relation');
        }
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityField(): string
    {
        return $this->entityField;
    }

    /**
     * @inheritdoc
     */
    public function setEntityField(string $entityField): TranslateTypeInterface
    {
        if ($this->id) {
            throw new LogicException('Cannot change translate relation');
        }
        $this->entityField = $entityField;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isSame(TranslateTypeInterface $translateType): bool
    {
        return get_class($translateType) === get_class($translateType)
            && (string)$this === (string)$translateType;
    }

    /**
     * @inheritdoc
     */
    public function isEqual(TranslateTypeInterface $translateType): bool
    {
        return $this->getEntityAlias() === $translateType->getEntityAlias()
            && $this->getEntityId() === $translateType->getEntityId()
            && $this->getEntityField() === $translateType->getEntityField()
            && $this->isSame($translateType);
    }
}