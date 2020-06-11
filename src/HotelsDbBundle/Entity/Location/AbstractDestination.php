<?php


namespace Apl\HotelsDbBundle\Entity\Location;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasBoundsViewportTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasCoordinatesTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasLocationAliasTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Behat\Transliterator\Transliterator;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class AbstractDestination
 * @package Apl\HotelsDbBundle\Entity\Location
 *
 * @ORM\MappedSuperclass()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="COUNTRY_IDX", columns={"country_id"}),
 *     @ORM\Index(name="ROOT_DESTINATION_IDX", columns={"root_destination_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractDestination implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface, \JsonSerializable
{
    use HasIntegerIdTrait,
        HasLocationAliasTrait,
        HasCoordinatesTrait,
        HasBoundsViewportTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    private const TRANSLATABLE_NAME = 'name';
    private const TRANSLATABLE_DESCRIPTION = 'description';

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Location\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     * @var Country
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Location\Destination")
     * @ORM\JoinColumn(name="root_destination_id", referencedColumnName="id", nullable=true)
     * @var Destination|null
     */
    private $rootDestination;

    /**
     * @ORM\Column(name="alias_prefix", type="string", nullable=true)
     * @var string|null
     */
    private $aliasPrefix;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getRootDestination()
            ? (string)$this->getRootDestination() . ' / ' . (string)$this->getName()
            : (string)$this->getName();
    }

    /**
     * @return string[]
     */
    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableString::class,
            self::TRANSLATABLE_DESCRIPTION => TranslatableText::class,
        ];
    }

    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('country'),
            new GetterAttribute('rootDestination'),
            EqualComparator::attributeFactory('alias'),
            new GetterAttribute('aliasPrefix'),
            EqualComparator::attributeFactory('coordinates'),
            EqualComparator::attributeFactory('bounds'),
            EqualComparator::attributeFactory('viewport'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new SetterAttribute('country'),
            new SetterAttribute('rootDestination'),
            ScalarHydrator::getStringAttribute('alias'),
            ScalarHydrator::getStringAttribute('aliasPrefix'),
            new SetterAttribute('coordinates'),
            new SetterAttribute('bounds'),
            new SetterAttribute('viewport')
        );
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     * @return $this
     */
    public function setCountry(?Country $country): AbstractDestination
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return Destination|null
     */
    public function getRootDestination(): ?Destination
    {
        return $this->rootDestination;
    }

    /**
     * @param Destination|null $rootDestination
     * @return $this
     */
    public function setRootDestination(?Destination $rootDestination): AbstractDestination
    {
        $this->rootDestination = $rootDestination;
        return $this;
    }

    /**
     * Alias for getTranslate(self::TRANSLATABLE_NAME)
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableString
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME, $locale);
    }

    /**
     * Alias for getTranslate(self::TRANSLATABLE_DESCRIPTION)
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableText
     */
    public function getDescription(Locale $locale = null): TranslatableText
    {
        return $this->getTranslate(self::TRANSLATABLE_DESCRIPTION, $locale);
    }

    /**
     * @return null|string
     */
    public function getAliasPrefix(): ?string
    {
        return $this->aliasPrefix;
    }

    /**
     * @param null|string $aliasPrefix
     * @return AbstractDestination
     */
    public function setAliasPrefix(?string $aliasPrefix): AbstractDestination
    {
        $this->aliasPrefix = $aliasPrefix;
        return $this;
    }

    /**
     * @ORM\PreFlush()
     */
    public function generateAliasOnPersist()
    {
        $prefix = [];

        if ($this->getRootDestination()) {
            $this->getRootDestination()->generateAliasOnPersist();
            $prefix[] = (string)$this->getRootDestination()->getAlias();
        } else if ($this->getCountry() && (string)$this->getCountry()->getAlias()) {
            $prefix[] = (string)$this->getCountry()->getAlias();
        }

        if ($this->getAliasPrefix()) {
            $prefix[] = $this->getAliasPrefix();
        }

        $prefix = implode(':', $prefix) . ':';

        if (
            (!(string)$this->getAlias() || mb_strpos((string)$this->getAlias(), $prefix) !== 0)
            && $name = (string)$this->getName(new Locale('en'))
        ) {
            $this->setAlias($prefix . Transliterator::transliterate($name));
        }
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => (string)$this->getName()
        ];
    }
}