<?php

namespace Apl\HotelsDbBundle\Entity\Dictionary;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectComparator;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractBoard
 * @package Apl\HotelsDbBundle\Entity\Dictionary
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractBoard implements TranslatableObjectInterface, HasGetterMappingInterface, HasSetterMappingInterface, \JsonSerializable
{
    private const TRANSLATABLE_NAME = 'name';

    use HasIntegerIdTrait,
        HasDateTimeCreatedTrait,
        HasTranslationTrait;

    /**
     * @ORM\Column(type="string", nullable=false, length=2, options={"fixed": true})
     * @var string|null
     * @Groups({"public"})
     */
    private $code;

    /**
     * @return string[]
     */
    public static function getTranslateMapping(): array
    {
        return [
            self::TRANSLATABLE_NAME => TranslatableString::class
        ];
    }

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return new SetterMapping(
            ScalarHydrator::getStringAttribute('code')
        );
    }

    public function getGetterMapping(): GetterMapping
    {
        return new GetterMapping(
            new GetterAttribute('code'),
            TranslatableObjectComparator::attributeFactory()
        );
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return AbstractBoard
     */
    public function setCode(?string $code): AbstractBoard
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param Locale|null $locale
     * @return TranslatableString
     * @Groups({"translate"})
     */
    public function getName(Locale $locale = null): TranslatableString
    {
        return $this->getTranslate(self::TRANSLATABLE_NAME, $locale);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'name' => $this->getName(),
        ];
    }

}