<?php


namespace Apl\HotelsDbBundle\Twig\Extensions;


use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Service\Money\Price\RoomPrice\CancellationPolicy;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Class HotelDbExtension
 *
 * @package Apl\HotelsDbBundle\Twig\Extensions
 */
class HotelDbExtension extends \Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('hotel_name_with_category', [$this, 'htmlHotelNameWithCategory'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('hotel_category', [$this, 'htmlHotelCategory'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param Hotel $hotel
     * @return string
     */
    public function htmlHotelNameWithCategory(Hotel $hotel): string
    {
        return "{$hotel->getName()}&nbsp;<span class=\"stars stars_{$hotel->getCategory()->getSimple()}\"></span>";
    }

    /**
     * @param Hotel $hotel
     * @return string
     */
    public function htmlHotelCategory(Hotel $hotel): string
    {
        return "<span class=\"stars stars_{$hotel->getCategory()->getSimple()}\"></span>";
    }
}