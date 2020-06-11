<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\Predictor;


use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ArrayGetterMapped;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ObjectDataManipulatorAwareTrait;
use Doctrine\Common\Util\ClassUtils;


/**
 * Class PriceRangePredictor
 *
 * @package Apl\HotelsDbBundle\Service\Price
 */
class PriceRangePredictor
{
    use ObjectDataManipulatorAwareTrait;

    /**
     * @param \DateTimeImmutable $firstNightDate дата начала первой ночи
     * @param \DateTimeImmutable $lastNightDate дата начала последней ночи
     * @param PredictablePriceInterface[] $existPrices
     * @return PredictablePriceInterface[]
     * @throws \Exception
     */
    public function predictNightCost(\DateTimeImmutable $firstNightDate, \DateTimeImmutable $lastNightDate, array $existPrices): array
    {
        usort($existPrices, function (PredictablePriceInterface $a, PredictablePriceInterface $b) {
            return $a->getPredictableMoney()->compare($b->getPredictableMoney());
        });

        $response = [];
        /** @var PredictablePriceInterface $leftPrice */
        $leftPrice = null;
        $currentDate = clone $firstNightDate;
        $period = new \DateInterval('P1D');
        foreach ($existPrices as $existPrice) {
            $diffDays = $existPrice->getPredictableDate()->diff($currentDate)->days;
            if ($diffDays) {
                $predicted = clone $existPrice->getPredictableMoney();

                // Если уже есть левая стоимость ищем среднее с текущией (правой), иначе будет крайний левый диапазон
                if ($leftPrice) {
                    $predicted->setAmount(bcdiv(bcadd($leftPrice->getPredictableMoney()->getAmount(), $predicted->getAmount(), 8), 2, 2));
                }

                // Дублируем стоимость на пропущенные диапазоны
                for (; $diffDays > 0; $diffDays--) {
                    $response[] = $this->createPredictedPrice($existPrice, $currentDate, clone $predicted);
                    $currentDate = $currentDate->add($period);
                }
            }

            $response[] = $existPrice;
            $currentDate = $currentDate->add($period);
            $leftPrice = $existPrice;
        }

        // Крайний правый диапазон
        for ($diffDays = $currentDate->diff($lastNightDate->add($period))->days; $diffDays > 0; $diffDays--) {
            $response[] = $this->createPredictedPrice($existPrice, $currentDate, clone $leftPrice->getPredictableMoney());
            $currentDate = $currentDate->add($period);
        }

        return $response;
    }

    /**
     * @param PredictablePriceInterface $price
     * @param \DateTimeImmutable $date
     * @param MoneyInterface $money
     * @return PredictablePriceInterface
     */
    private function createPredictedPrice(PredictablePriceInterface $price, \DateTimeImmutable $date, MoneyInterface $money): PredictablePriceInterface
    {
        $className = ClassUtils::getClass($price);

        // Нельзя просто взять и склонировать доктриновскую сущность
        $predicted = new $className;

        $this->objectDataManipulator->hydrate($predicted, $price);
        $this->objectDataManipulator->hydrate($predicted, new ArrayGetterMapped([
            'predicted' => true,
            'predictableDate' => $date,
            'predictableMoney' => $money,
        ]));

        return $predicted;
    }
}