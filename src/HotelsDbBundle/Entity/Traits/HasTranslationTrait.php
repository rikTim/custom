<?php


namespace Base\HotelsDbBundle\Entity\Traits;


use Base\HotelsDbBundle\Entity\Locale;
use Base\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Base\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Base\HotelsDbBundle\Exception\LogicException;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslatesCollection;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Trait HasTranslationTrait
 * @package Base\HotelsDbBundle\Entity\Traits
 */
trait HasTranslationTrait
{
    /**
     * @var TranslatesCollection
     */
    private $translatesCollection;

    /**
     * @return string[]
     */
    abstract public static function getTranslateMapping(): array;

    /**
     * @return string
     */
    public static function getTranslateAlias(): string
    {
        return ClassUtils::getRealClass(get_called_class());
    }

    /**
     * @return bool
     */
    public static function isAllowProbabilisticResolve(): bool
    {
        return true;
    }

    /**
     * @param TranslatesCollection $translatesCollection
     */
    public function setTranslatesCollection(TranslatesCollection $translatesCollection): void
    {
        $this->translatesCollection = $translatesCollection;
    }

    /**
     * @return bool
     */
    public function hasTranslatesCollection(): bool
    {
        return !empty($this->translatesCollection);
    }

    /**
     * @return TranslatesCollection
     */
    public function getTranslatesCollection(): TranslatesCollection
    {
        return $this->translatesCollection;
    }

    /**
     * @return int|null
     */
    public function getTranslateId(): ?int
    {
        if (method_exists($this, 'getId')) {
            $id = $this->getId();
            if (is_int($id) || is_null($id)) {
                return $id;
            }
        }

        throw new LogicException(sprintf('Class "%s" must has implementation of method getTranslateId', get_class($this)));
    }

    /**
     * Proxy method to simple get translate from collection
     *
     * @param string $fieldName
     * @param Locale|null $locale
     * @return TranslateTypeInterface|TranslatableString|TranslatableText
     */
    public function getTranslate(string $fieldName, Locale $locale = null): TranslateTypeInterface
    {
        return $this->getTranslatesCollection()->getTranslate($fieldName, $locale);
    }
}