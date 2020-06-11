<?php


namespace Apl\HotelsDbBundle\Service\Money;


use Apl\HotelsDbBundle\Exception\InvalidArgumentException;

/**
 * Trait MoneyCalculatorTrait
 *
 * @package Apl\HotelsDbBundle\Service\Money
 */
trait MoneyCalculatorTrait
{
    /**
     * @return string
     */
    abstract public function getAmount(): string;

    /**
     * @param null|string $amount
     * @return MoneyInterface
     */
    abstract public function setAmount(?string $amount): MoneyInterface;

    /**
     * @param int $precision
     * @return string
     */
    public function getRoundAmount(int $precision = 0): string
    {

        $amount = $this->getAmount();
        if (strpos($amount, '.') !== false) {
            return round($amount,$precision,PHP_ROUND_HALF_DOWN);
        }
        return $amount;
    }

    /**
     * @param MoneyInterface $money
     * @param int $scale
     * @return MoneyInterface|self
     */
    public function add(MoneyInterface $money, int $scale = 2): MoneyInterface
    {
        $this->assertSameCurrency($money);
        $this->setAmount(bcadd($this->getAmount(), $money->getAmount(), $scale));
        return $this;
    }

    /**
     * @param MoneyInterface $money
     * @param int $scale
     * @return MoneyInterface|self
     */
    public function sub(MoneyInterface $money, int $scale = 2): MoneyInterface
    {
        $this->assertSameCurrency($money);
        $this->setAmount(bcsub($this->getAmount(), $money->getAmount(), $scale));
        return $this;
    }

    /**
     * @param string $multiplier
     * @return MoneyInterface|self
     */
    public function mul(string $multiplier): MoneyInterface
    {
        $this->setAmount(bcmul($this->getAmount(), $multiplier, 2));
        return $this;
    }

    /**
     * @param MoneyInterface $money
     */
    private function assertSameCurrency(MoneyInterface $money): void
    {
        if ($this->amount === null) {
            $this->amount = '0';
        }

        if ($this->currency === null) {
            $this->currency = $money->getCurrency();
        } else if ($this->getCurrency() !== $money->getCurrency()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Given money has different currency: "%s" expected, "%s" given',
                    $this->getCurrency(),
                    $money->getCurrency()
                )
            );
        }
    }
}