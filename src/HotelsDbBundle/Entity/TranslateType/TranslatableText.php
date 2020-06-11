<?php


namespace Apl\HotelsDbBundle\Entity\TranslateType;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class TranslatableText
 * @package Apl\HotelsDbBundle\Entity\TranslateType
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\TranslateType\TranslatableTextRepository")
 * @ORM\Table(name="hotels_db_translatable_text", indexes={
 *      @ORM\Index(name="TRANSLATE_FULLTEXT_TEXT", columns={"value"}, options={"fulltext"}),
 * })
 */
class TranslatableText extends AbstractTranslateType implements \JsonSerializable
{
    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $value;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getValue();
    }

    /**
     * @Groups({"translate"})
     * @return string
     */
    public function getValue(): string
    {
        return (string)$this->value;
    }

    /**
     * @param string $value
     * @return TranslatableText
     */
    public function setValue(string $value): TranslateTypeInterface
    {
        $this->value = trim($value);
        return $this;
    }

    /**
     * @param TranslateTypeInterface $translateType
     * @return $this
     */
    public function merge(TranslateTypeInterface $translateType): TranslateTypeInterface
    {
        if (!($translateType instanceof TranslatableText)) {
            throw new InvalidArgumentException(
                sprintf('Can`t merge TranslatableString with "%s"', get_class($translateType))
            );
        }

        return $this->setValue($translateType->getValue());
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'locale' => $this->getLocale(),
            'value' => $this->getValue()
        ];
    }
}