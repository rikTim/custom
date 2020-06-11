<?php


namespace Apl\HotelsDbBundle\Entity\Money\Price;


use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\ClientRoomPriceInterface;
use Apl\HotelsDbBundle\Service\Money\Price\Predictor\PredictablePriceInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\DateTimeHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class MinimumPrice
 *
 * @package Apl\PriceBundle\Entity
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Money\MinimumPriceRepository")
 * @ORM\Table(name="hotels_db_rate_minimum_price", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *          name="PRICE_HOTEL_UNIQUE",
 *          columns={"hotel_id", "adults", "children", "board_id", "room_quantity", "check_in", "check_out"}
 *     )
 *   },
 *   indexes={
 *          @ORM\Index(name="HOTEL_INDEX", columns={"hotel_id"}),
 *          @ORM\Index(name="ROOM_INDEX", columns={"room_id"}),
 *          @ORM\Index(name="BOARD_INDEX", columns={"board_id"}),
 *          @ORM\Index(name="PROVIDER_ALIAS_INDEX", columns={"service_provider_alias"}),
 *          @ORM\Index(name="UPDATED_INDEX", columns={"updated"})
 *   }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class MinimumPrice implements PredictablePriceInterface, ClientRoomPriceInterface
{
    use HasIntegerIdTrait,
        RoomPriceTrait {
            RoomPriceTrait::getSetterMapping as private roomPriceSetterMapping;
        }

    /**
     * @var bool
     */
    private $predicted = false;

    /**
     * @var Money
     */
    private $rateClient;

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return $this->roomPriceSetterMapping()
            ->addAttribute(ScalarHydrator::getBooleanAttribute('predicted'))
            // Добавляем виртуальные колонки
            ->addAttribute(DateTimeHydrator::attributeFactory('date', 'setCheckIn'))
            ->addAttribute(new SetterAttribute('money', 'setRateClient'));
    }

    /**
     * @return bool
     */
    public function isPredicted(): bool
    {
        return $this->predicted;
    }

    /**
     * @param bool $predicted
     * @return MinimumPrice
     */
    public function setPredicted(bool $predicted): MinimumPrice
    {
        $this->predicted = $predicted;
        return $this;
    }


    /**
     * @return \DateTimeImmutable
     */
    public function getPredictableDate(): \DateTimeImmutable
    {
        $checkIn = $this->getCheckIn();
        if ($this->getCheckOut() && $checkIn->diff($this->getCheckOut())->days !== 1) {
            throw new RuntimeException('Predictable price must have only one night coast');
        }

        return $this->getCheckIn();
    }

    /**
     * @return MoneyInterface
     */
    public function getPredictableMoney(): MoneyInterface
    {
        return $this->getRateNet();
    }

    /**
     * @return MoneyInterface
     */
    public function getRateClient(): MoneyInterface
    {
        return $this->rateClient ?? $this->rateClient = new Money(0, MoneyInterface::EMPTY_CURRENCY);
    }
}