<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;

/**
 * Trait HasMoneyTrait
 *
 * @package Apl\HotelsDbBundle\Entity\Traits
 */
trait HasMoneyTrait
{
    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Money\Money", columnPrefix=false)
     * @var Money
     */
    private $money;

    /**
     * @return \Apl\HotelsDbBundle\Entity\Money\Price\\Apl\HotelsDbBundle\Service\Money\MoneyInterface
     */
    public function getMoney(): MoneyInterface
    {
        return $this->money;
    }

    /**
     * @param \Apl\HotelsDbBundle\Entity\Money\Price\\Apl\HotelsDbBundle\Service\Money\MoneyInterface|\Apl\HotelsDbBundle\Entity\Money\Money $money
     * @return $this
     */
    public function setMoney(MoneyInterface $money)
    {
        if (!($money instanceof Money)) {
            throw new InvalidArgumentException('Incorrect money object');
        }

        $this->money = $money;
        return $this;
    }
}