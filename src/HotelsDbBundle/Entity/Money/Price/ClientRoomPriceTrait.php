<?php


namespace Apl\HotelsDbBundle\Entity\Money\Price;


use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\ClientRoomPriceInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ComparableInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class ClientRoomPriceTrait
 *
 * @package HotelsBundle\Entity\Money\Price
 */
trait ClientRoomPriceTrait
{
    use RoomPriceTrait {
        RoomPriceTrait::getGetterMapping as private roomPriceGetterMapping;
        RoomPriceTrait::getSetterMapping as private roomPriceSetterMapping;
        RoomPriceTrait::compare as private roomPriceCompare;
    }

    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\Money\Money", columnPrefix="rate_client_")
     * @Groups({"secured"})
     * @var MoneyInterface
     */
    private $rateClient;

    /**
     * {@inheritdoc}
     */
    public function getGetterMapping(): GetterMapping
    {
        return $this->roomPriceGetterMapping()
            ->addAttribute(EqualComparator::attributeFactory('rateClient'));
    }

    /**
     * {@inheritdoc}
     */
    public function getSetterMapping(): SetterMapping
    {
        return $this->roomPriceSetterMapping()
            ->addAttribute(new SetterAttribute('rateClient'));
    }

    /**
     * @return MoneyInterface
     */
    public function getRateClient(): MoneyInterface
    {
        return $this->rateClient ?? $this->rateClient = new Money(0, MoneyInterface::EMPTY_CURRENCY);
    }

    /**
     * @param MoneyInterface $rateClient
     * @return ClientRoomPriceInterface|self
     */
    public function setRateClient(MoneyInterface $rateClient): ClientRoomPriceInterface
    {
        $this->rateClient = $rateClient;
        return $this;
    }

    /**
     * @param ComparableInterface $item
     * @return int
     */
    public function compare(ComparableInterface $item): int
    {
        if (!($item instanceof ClientRoomPriceInterface) || \get_class($this) !== \get_class($item)) {
            throw new InvalidArgumentException(sprintf('Cannot compare "%s" with different class', \get_class($this)));
        }

        // Нельзя сравнивать разные валюты, но мы должны сказать что-то в ответе без ошибки
        if ($this->getRateClient()->getCurrency() !== $item->getRateClient()->getCurrency()) {
            return strcasecmp($this->getRateClient()->getCurrency(), $item->getRateClient()->getCurrency());
        }

        // Разница в цене
        if (($compareRate = $this->getRateClient()->compare($item->getRateClient())) !== 0) {
            return $compareRate;
        }

        return $this->roomPriceCompare($item);
    }
}