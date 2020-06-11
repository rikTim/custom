<?php


namespace Apl\HotelsDbBundle\Entity\Hotel;


use Apl\HotelsDbBundle\Entity\Dictionary\Accommodation;
use Apl\HotelsDbBundle\Entity\Dictionary\Category;
use Apl\HotelsDbBundle\Entity\Dictionary\Chain;
use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Location\Country;
use Apl\HotelsDbBundle\Entity\Location\Destination;
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
 * Class State
 * @package Apl\HotelsDbBundle\Entity\Destination
 *
 * @ORM\MappedSuperclass()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="COUNTRY_IDX", columns={"country_id"}),
 *     @ORM\Index(name="DESTINATION_IDX", columns={"destination_id"}),
 *     @ORM\Index(name="CATEGORY_IDX", columns={"category_id"}),
 *     @ORM\Index(name="CHAIN_IDX", columns={"CHAIN_id"}),
 *     @ORM\Index(name="ACCOMMODATION_IDX", columns={"accommodation_id"}),
 * })
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractHotel implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface, \JsonSerializable
{
    use HasIntegerIdTrait,
        HasLocationAliasTrait,
        HasCoordinatesTrait,
        HasBoundsViewportTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    private const TRANSLATABLE_NAME = 'name';
    private const TRANSLATABLE_DESCRIPTION = 'description';
    private const TRANSLATABLE_ADDRESS = 'address';

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Location\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     * @var Country|null
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Location\Destination")
     * @ORM\JoinColumn(name="destination_id", referencedColumnName="id", nullable=false)
     * @var Destination|null
     */
    private $destination;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $web;

    /**
     * Security and Heals rating, not stars!
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $S2C;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     * @var Category|null
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Chain")
     * @ORM\JoinColumn(name="chain_id", referencedColumnName="id", nullable=true)
     * @var Chain|null
     */
    private $chain;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Accommodation")
     * @ORM\JoinColumn(name="accommodation_id", referencedColumnName="id", nullable=true)
     * @var Accommodation|null
     */
    private $accommodation;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     * @var boolean
     */
    private $published;

    /**
     * @return string[]
     */
    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableString::class,
            self::TRANSLATABLE_DESCRIPTION => TranslatableText::class,
            self::TRANSLATABLE_ADDRESS => TranslatableText::class,
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->hasTranslatesCollection() && $this->getName()
            ? (string)$this->getName() . " ({$this->getCountry()} / {$this->getDestination()})"
            : 'Empty hotel';
    }

    /**
     * @inheritdoc
     */
    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('country'),
            new GetterAttribute('destination'),
            EqualComparator::attributeFactory('alias'),
            new GetterAttribute('postalCode'),
            new GetterAttribute('email'),
            new GetterAttribute('web'),
            new GetterAttribute('S2C'),
            new GetterAttribute('published'),
            EqualComparator::attributeFactory('category'),
            EqualComparator::attributeFactory('chain'),
            EqualComparator::attributeFactory('accommodation'),
            EqualComparator::attributeFactory('coordinates'),
            EqualComparator::attributeFactory('bounds'),
            EqualComparator::attributeFactory('viewport'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * @inheritdoc
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            new SetterAttribute('country'),
            new SetterAttribute('destination'),
            ScalarHydrator::getStringAttribute('alias'),
            ScalarHydrator::getStringAttribute('postalCode'),
            ScalarHydrator::getStringAttribute('email'),
            ScalarHydrator::getStringAttribute('web'),
            ScalarHydrator::getStringAttribute('S2C'),
            ScalarHydrator::getBooleanAttribute('published'),
            new SetterAttribute('category'),
            new SetterAttribute('chain'),
            new SetterAttribute('accommodation'),
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
     * @param Country|null $country
     * @return AbstractHotel
     */
    public function setCountry(?Country $country): AbstractHotel
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return Destination|null
     */
    public function getDestination(): ?Destination
    {
        return $this->destination;
    }

    /**
     * @param Destination|null $destination
     * @return AbstractHotel
     */
    public function setDestination(?Destination $destination): AbstractHotel
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param null|string $postalCode
     * @return AbstractHotel
     */
    public function setPostalCode(?string $postalCode): AbstractHotel
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     * @return AbstractHotel
     */
    public function setEmail(?string $email): AbstractHotel
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getWeb(): ?string
    {
        return $this->web;
    }

    /**
     * @param null|string $web
     * @return AbstractHotel
     */
    public function setWeb(?string $web): AbstractHotel
    {
        $this->web = $web;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getS2C(): ?string
    {
        return $this->S2C;
    }

    /**
     * @param null|string $S2C
     * @return AbstractHotel
     */
    public function setS2C(?string $S2C): AbstractHotel
    {
        $this->S2C = $S2C;
        return $this;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     * @return AbstractHotel
     */
    public function setCategory(?Category $category): AbstractHotel
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Chain|null
     */
    public function getChain(): ?Chain
    {
        return $this->chain;
    }

    /**
     * @param Chain|null $chain
     * @return AbstractHotel
     */
    public function setChain(?Chain $chain): AbstractHotel
    {
        $this->chain = $chain;
        return $this;
    }

    /**
     * @return Accommodation|null
     */
    public function getAccommodation(): ?Accommodation
    {
        return $this->accommodation;
    }

    /**
     * @param Accommodation|null $accommodation
     * @return AbstractHotel
     */
    public function setAccommodation(?Accommodation $accommodation): AbstractHotel
    {
        $this->accommodation = $accommodation;
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
     * Alias for getTranslate(self::TRANSLATABLE_ADDRESS)
     * @param Locale|null $locale
     * @return TranslatableText
     */
    public function getAddress(Locale $locale = null): TranslatableText
    {
        $address = $this->getTranslate(self::TRANSLATABLE_ADDRESS, $locale);
        if (preg_replace('/[\.\,\s]+/', '', (string)$address) === '') {
            $address->setValue($this->getDestination()->getName() . ', ' . $this->getCountry()->getName());
        }

        return $address;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @param bool $published
     * @return AbstractHotel
     */
    public function setPublished(bool $published = true): AbstractHotel
    {
        $this->published = $published;
        return $this;
    }

    /**
     * @ORM\PreFlush()
     */
    public function generateAliasOnPersist()
    {
        if (!(string)$this->getAlias() && $name = (string)$this->getName(new Locale('en'))) {
            $this->setAlias(Transliterator::transliterate($name));
        }
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'hotelId' => $this->getId(),
            'name' => $this->getName(),
            'country' => $this->getCountry(),
            'destination' => $this->getDestination(),
        ];
    }
}