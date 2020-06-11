<?php


namespace Apl\HotelsDbBundle\Entity;


use Apl\HotelsDbBundle\Exception\RuntimeException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class Locale
 * @package Apl\HotelsDbBundle\Entity
 *
 * @ORM\Embeddable()
 */
class Locale
{
    public const LOCALE_UK_UA = 'uk_UA';
    public const LOCALE_RU_UA = 'ru_UA';
    public const LOCALE_RU_RU = 'ru_RU';
    public const LOCALE_EN_US = 'en_US';
    public const LOCALE_EN_GB = 'en_GB';

    public const ISO639_2_LETTER = 0;
    public const ISO639_3_LETTER = 1;

    public const ISO639_DICTIONARY = [
        self::LOCALE_UK_UA => ['uk', 'ukr'],
        self::LOCALE_RU_UA => ['ru', 'rus'],
        self::LOCALE_RU_RU => ['ru', 'rus'],
        self::LOCALE_EN_US => ['en', 'eng'],
        self::LOCALE_EN_GB => ['en', 'eng'],
    ];

    /**
     * @ORM\Column(name="locale", type="string", length=5, options={"fixed": true})
     * @var string
     * @Groups({"public", "translate"})
     */
    private $locale;

    /**
     * @param string $code
     * @param int $iso639Type
     * @return string
     */
    public static function getFromLanguageCode(string $code, int $iso639Type = self::ISO639_2_LETTER): string
    {
        $code = mb_strtolower($code);

        foreach (self::ISO639_DICTIONARY as $locale => $codes) {
            if ($codes[$iso639Type] === $code) {
                return $locale;
            }
        }

        throw new RuntimeException(sprintf('Cannot create locale from code "%s" for iso type %u', $code, $iso639Type));
    }

    /**
     * Locale constructor.
     * @param string $input
     */
    public function __construct(string $input)
    {
        if (isset(self::ISO639_DICTIONARY[$input])) {
            $this->locale = $input;
        } else {
            switch (\strlen($input)) {
                case 2:
                    $this->locale = self::getFromLanguageCode($input, self::ISO639_2_LETTER);
                    break;

                case 3:
                    $this->locale = self::getFromLanguageCode($input, self::ISO639_3_LETTER);
                    break;

                case 5:
                    throw new RuntimeException(sprintf('Locale with code "%s" not enabled', $input));

                default:
                    throw new RuntimeException(sprintf('Locale has incorrect format "%s"', $input));
            }
        }
    }

    /**
     * @return string
     */
    public function getLocale() : string
    {
        return $this->locale;
    }

    public function __toString()
    {
        return $this->getLocale();
    }

    /**
     * @param int $iso639Type
     * @return string
     */
    public function getLanguageCode(int $iso639Type = self::ISO639_2_LETTER) : string
    {
        return self::ISO639_DICTIONARY[$this->locale][$iso639Type];
    }

    public function isEqual(Locale $locale)
    {
        return $this->getLocale() === $locale->getLocale();
    }
}