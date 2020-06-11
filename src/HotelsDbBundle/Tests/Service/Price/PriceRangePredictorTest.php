<?php


namespace Apl\HotelsDbBundle\Tests\Service\Price;


use Apl\HotelsDbBundle\Entity\Money\Money;
use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\Money\Price\Predictor\PredictablePriceInterface;

/**
 * Class PriceRangePredictorTest
 *
 * @package Apl\HotelsDbBundle\Tests\Service\Price
 */
class PriceRangePredictorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group price
     */
    public function testPredictNightCost()
    {
        $service = new \Apl\HotelsDbBundle\Service\Money\Price\Predictor\PriceRangePredictor();

        $predictableMock = $this->getMockForAbstractClass(PredictablePriceInterface::class, [], '', false, false, true, [
            'getDate', 'getMoney', 'createPredicted'
        ]);

        // Case 1: single value
        $existPrices = [];
        $exist = clone $predictableMock;
        $exist->expects($this->once())->method('getDate')->willReturn(new \DateTimeImmutable('2020-01-03'));
        $exist->expects($this->atMost(0))->method('getMoney')->willReturn((new Money())->setAmount(100)->setCurrency('EUR'));

        $exist->expects($this->exactly(4))->method('createPredicted')
            ->withConsecutive(function(\DateTimeImmutable $date, MoneyInterface $predicted) use ($predictableMock) {
                static $index = 0;
                switch ($index++) {
                    case 0: $this->assertSame('2020-01-01', $date->format('Y-m-d')); break;
                    case 1: $this->assertSame('2020-01-02', $date->format('Y-m-d')); break;
                    case 2: $this->assertSame('2020-01-04', $date->format('Y-m-d')); break;
                    case 3: $this->assertSame('2020-01-05', $date->format('Y-m-d')); break;
                    default:
                        throw new \Exception('Incorrect call, expected not call');
                        break;
                }

                $this->assertSame('100', $predicted->getAmount());
                $this->assertSame('EUR', $predicted->getCurrency());

                return clone $predictableMock;
            });
        $existPrices[] = $exist;

        $response = $service->predictNightCost(new \DateTimeImmutable('2020-01-01'), new \DateTimeImmutable('2020-01-05'), $existPrices);
        $this->assertCount(5, $response);


        // Case 2: multiply values with different money amount
        $existPrices = [];

        $exist = clone $predictableMock;
        $exist->expects($this->atMost(0))->method('getDate')->willReturn(new \DateTimeImmutable('2020-01-02'));
        $exist->expects($this->atMost(0))->method('getMoney')->willReturn((new Money())->setAmount(100)->setCurrency('EUR'));
        $exist->expects($this->exactly(1))->method('createPredicted')
            ->withConsecutive(function(\DateTimeImmutable $date, \Apl\HotelsDbBundle\Service\Money\MoneyInterface $predicted) use ($predictableMock) {
                $this->assertSame('2020-01-01', $date->format('Y-m-d'));
                $this->assertSame('100', $predicted->getAmount());
                $this->assertSame('EUR', $predicted->getCurrency());

                return clone $predictableMock;
            });
        $existPrices[] = $exist;

        $exist = clone $predictableMock;
        $exist->expects($this->atMost(0))->method('getDate')->willReturn(new \DateTimeImmutable('2020-01-05'));
        $exist->expects($this->atMost(0))->method('getMoney')->willReturn((new Money())->setAmount(200)->setCurrency('EUR'));
        $exist->expects($this->exactly(3))->method('createPredicted')
            ->withConsecutive(function(\DateTimeImmutable $date, \Apl\HotelsDbBundle\Service\Money\MoneyInterface $predicted) use ($predictableMock) {
                static $index = 0;
                switch ($index++) {
                    case 0:
                        $this->assertSame('2020-01-03', $date->format('Y-m-d'));
                        $this->assertSame('150', $predicted->getAmount());
                        break;
                    case 1:
                        $this->assertSame('2020-01-04', $date->format('Y-m-d'));
                        $this->assertSame('150', $predicted->getAmount());
                        break;
                    case 2:
                        $this->assertSame('2020-01-06', $date->format('Y-m-d'));
                        $this->assertSame('200', $predicted->getAmount());
                        break;
                    default:
                        throw new \Exception('Incorrect call, expected not call');
                        break;
                }


                $this->assertSame('EUR', $predicted->getCurrency());

                return clone $predictableMock;
            });
        $existPrices[] = $exist;
        $response = $service->predictNightCost(new \DateTimeImmutable('2020-01-01'), new \DateTimeImmutable('2020-01-06'), $existPrices);
        $this->assertCount(6, $response);
    }
}