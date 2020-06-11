<?php


namespace Apl\HotelsDbBundle\Entity\Money;


use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\Money\MoneyCalculatorTrait;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class Money
 *
 * @package Apl\HotelsDbBundle\Entity\Money\Money
 *
 * @ORM\Embeddable()
 */
class Money implements MoneyInterface, \Serializable, \JsonSerializable
{
    use MoneyCalculatorTrait;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     * @var string
     */
    protected $amount;

    /**
     * @ORM\Column(type="string", length=3, nullable=false, options={"fixed": true})
     * @var string
     */
    protected $currency;

    /**
     * @var \Money\Money
     * @deprecated
     */
    private $money;

    /**
     * Money constructor.
     *
     * @param string|null $amount
     * @param string|null $currency
     */
    public function __construct(string $amount = null, string $currency = null)
    {
        $this->setAmount($amount);
        $this->setCurrency($currency);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getAmount() . ' ' . $this->getCurrency();
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount ?? '0';
    }

    /**
     * @param string|null $amount
     * @return \Apl\HotelsDbBundle\Service\Money\MoneyInterface
     */
    public function setAmount(?string $amount): MoneyInterface
    {
        $this->amount = $amount ?? '0';
        $this->money = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency ?? self::EMPTY_CURRENCY;
    }

    /**
     * @param string|null $currency
     * @return MoneyInterface
     */
    public function setCurrency(?string $currency): MoneyInterface
    {
        $this->currency = $currency ? mb_strtoupper($currency) : null;
        $this->money = null;
        return $this;
    }

    /**
     * @return \Money\Money
     * @throws \InvalidArgumentException
     * @deprecated
     */
    public function getMoney(): \Money\Money
    {
        \assert($this->currency !== null && $this->amount !== null, 'Empty currency (' . $this->currency . ')  or amount (' . $this->amount . ')');

        if (!$this->money) {
            $this->money = new \Money\Money(
                (int)bcmul($this->amount, '100', 0),
                new \Money\Currency($this->currency)
            );
        }
        return $this->money;
    }

    /**
     * @param \Money\Money $money
     * @return \Apl\HotelsDbBundle\Service\Money\MoneyInterface
     * @deprecated
     */
    public function setMoney(\Money\Money $money): MoneyInterface
    {
        $this->money = $money;
        $this->amount = bcdiv($money->getAmount(), '100', 2);
        $this->currency = $money->getCurrency()->getCode();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function compare(ComparableInterface $item): int
    {
        if (!\is_object($item) || !($item instanceof self) || \get_class($this) !== \get_class($item)) {
            throw new InvalidArgumentException('Given money has different class');
        }

        $this->assertSameCurrency($item);
        return bccomp($this->getAmount(), $item->getAmount(), 2);
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    public function isEqual(ComparableInterface $item): bool
    {
        return $this->compare($item) === 0;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->getCurrency() !== self::EMPTY_CURRENCY;
    }

    /**
     * String representation of object
     *
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return \serialize($this->toArray());
    }

    /**
     * Constructs the object
     *
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $unserialized = \unserialize($serialized, ['allowed_classes' => false]);
        $this->setCurrency($unserialized['currency'])->setAmount($unserialized['amount']);
    }

    /**
     * @param array $data
     * @return MoneyInterface
     */
    public static function createFromArray(array $data): MoneyInterface
    {
        return new self($data['amount'], $data['currency']);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['amount' => $this->getAmount(), 'currency' => $this->getCurrency()];
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}