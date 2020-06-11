<?php


namespace Apl\HotelsDbBundle\Entity\Money\Price;


use Apl\HotelsDbBundle\Service\Money\Price\ClientRoomPrice\ClientRoomPriceInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractClientRoomPrice
 *
 * @package Apl\HotelsDbBundle\Entity\Money\Price
 * @ORM\MappedSuperclass()
 */
abstract class AbstractClientRoomPrice implements ClientRoomPriceInterface
{
    use ClientRoomPriceTrait;
}