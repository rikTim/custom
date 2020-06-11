<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\Predictor;


use Apl\HotelsDbBundle\Service\Money\MoneyInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;

/**
 * Interface PredictablePriceInterface
 *
 * @package Apl\HotelsDbBundle\Service\Price
 */
interface PredictablePriceInterface extends HasGetterMappingInterface, HasSetterMappingInterface
{
    /**
     * @return bool
     */
    public function isPredicted(): bool;

    /**
     * @return \DateTimeImmutable
     */
    public function getPredictableDate(): \DateTimeImmutable;

    /**
     * @return \Apl\HotelsDbBundle\Entity\Money\Price\MoneyInterface
     */
    public function getPredictableMoney(): MoneyInterface;
}