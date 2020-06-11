<?php


namespace Apl\HotelsDbBundle\Twig\Extensions;


use Apl\HotelsDbBundle\Service\Money\MoneyInterface;

/**
 * Class MoneyExtension
 *
 * @package Apl\HotelsDbBundle\Twig\Extensions
 */
class MoneyExtension extends \Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('format_money', [$this, 'formatMoney'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param MoneyInterface $money
     * @param int $precision
     * @param int $fractionDigits
     * @return string
     */
    public function formatMoney(MoneyInterface $money, int $precision = 0, int $fractionDigits = 0): string
    {
        $locale = \Locale::getDefault();
        $locale = $money->getCurrency() !== 'UAH' && ($locale === 'uk' || $locale === 'uk-UA') ? 'RU' : $locale;

        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        // This is correct use NumberFormatter
        // @see comment http://php.net/manual/en/numberformatter.formatcurrency.php#114376
        $formatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $money->getCurrency());
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $fractionDigits);

        return $formatter->formatCurrency($money->getRoundAmount($precision), $money->getCurrency());
    }
}