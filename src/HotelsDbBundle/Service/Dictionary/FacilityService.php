<?php


namespace Apl\HotelsDbBundle\Service\Dictionary;


use Apl\HotelsDbBundle\Entity\Hotel\Hotel;

class FacilityService
{

    /**
     * @param $hotels
     * @return array
     */
    public function getSortedFacilityForHotels($hotels): array
    {
        /** @var  Hotel $hotel */
        $facilityList = [];
        foreach ($hotels as $hotel) {
            $hotelFacility = $this->getSortedFacilityForOneHotel($hotel);
            krsort($hotelFacility);
            $facilityList[$hotel->getId()] = $hotelFacility;
        }


        return $facilityList;
    }

    /**
     * @param Hotel $hotel
     * @param bool $sortBy
     * @return array
     */
    public function getSortedFacilityForOneHotel(Hotel $hotel, $sortBy = false): array
    {
        $hotelFacility = [];
        foreach ($hotel->getFacilities() as $facility) {
            if (($priory = $facility->getFacility()->getGroup()->getPriory()) !== null) {
                $hotelFacility[$priory][] = $facility;
            }
        }
        if (!empty($hotelFacility) && $sortBy) {
           ksort($hotelFacility);
        }
        return $hotelFacility;
    }
}