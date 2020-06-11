<?php


namespace Apl\HotelsDbBundle\Entity\TranslateType;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class TranslatableString
 * @package Apl\HotelsDbBundle\Entity\Translate
 *
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\TranslateType\TranslatableStringRepository")
 * @ORM\Table(name="hotels_db_translatable_string", indexes={
 *      @ORM\Index(name="TRANSLATE_FULLTEXT_STRING", columns={"nominative"}, options={"fulltext"}),
 * })
 */
class TranslatableString extends AbstractTranslateType implements \JsonSerializable
{
    /**
     * Именительный падеж
     */
    const CASE_NOMINATIVE = 'nominative';

    /**
     * Родительный
     */
    const CASE_GENITIVE = 'genitive';

    /**
     * Дательный
     */
    const CASE_DATIVE = 'dative';

    /**
     * Винительный
     */
    const CASE_ACCUSATIVE = 'accusative';

    /**
     * Творительный
     */
    const CASE_INSTRUMENTAL = 'instrumental';

    /**
     * Предложный
     */
    const CASE_LOCATIVE = 'locative';

    /**
     * Звательный (укр)
     */
    const CASE_VOCATIVE = 'vocative';

    /**
     * Аблатив:  «откуда?», «от кого?», «от чего?», «отчего?». (нет в русском)
     */
    const CASE_ABLATIVE = 'ablative';

    /**
     * Список доступных падежей
     */
    const AVAILABLE_CASES = [
        self::CASE_NOMINATIVE,
        self::CASE_GENITIVE,
        self::CASE_DATIVE,
        self::CASE_ACCUSATIVE,
        self::CASE_INSTRUMENTAL,
        self::CASE_LOCATIVE,
        self::CASE_VOCATIVE,
    ];

    /**
     * @ORM\Column(type="string", nullable=false, length=255)
     * @var string
     */
    private $nominative;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @var string
     */
    private $genitive;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @var string
     */
    private $dative;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @var string
     */
    private $accusative;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @var string
     */
    private $instrumental;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @var string
     */
    private $locative;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @var string
     */
    private $vocative;

    /**
     * @param string $case
     * @return string
     * @Groups({"translate"})
     */
    public function getValue(string $case = self::CASE_NOMINATIVE): string
    {
        return (string)$this->{$this->getCaseFieldName($case)};
    }

    /**
     * @param string $value
     * @param string $case
     * @return TranslatableString
     */
    public function setValue(string $value, string $case = self::CASE_NOMINATIVE): TranslateTypeInterface
    {
        if (mb_strlen($value) > 255) {
            throw new InvalidArgumentException('Translate for string is to long. Maximum 255 chars');
        }

        $this->{$this->getCaseFieldName($case)} = trim($value);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * @param TranslateTypeInterface $translateType
     * @return TranslatableString
     */
    public function merge(TranslateTypeInterface $translateType): TranslateTypeInterface
    {
        if (!($translateType instanceof TranslatableString)) {
            throw new InvalidArgumentException(
                sprintf('Can`t merge TranslatableString with "%s"', get_class($translateType))
            );
        }

        foreach (self::AVAILABLE_CASES as $case) {
            if ($translateType->getValue($case) !== null) {
                $this->setValue($translateType->getValue($case), $case);
            }
        }

        return $this;
    }

    /**
     * @param TranslateTypeInterface|TranslatableString $translateType
     * @return bool
     */
    public function isSame(TranslateTypeInterface $translateType): bool
    {
        if (parent::isSame($translateType)) {
            foreach (self::AVAILABLE_CASES as $case) {
                if ($this->getValue($case) !== $translateType->getValue($case)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $case
     * @return string
     */
    private function getCaseFieldName(string $case) : string
    {
        $case = strtolower($case);
        if (!in_array($case, self::AVAILABLE_CASES, true)) {
            throw new InvalidArgumentException(sprintf('Translate case "%s" not available', $case));
        }

        return $case;
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