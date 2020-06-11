<?php


namespace Apl\HotelsDbBundle\Repository\Hotel;


use Apl\HotelsDbBundle\Repository\Traits\FindByAliasTrait;
use Apl\HotelsDbBundle\Service\Search\SearchableRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class HotelRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Hotel
 */
class HotelRepository extends EntityRepository implements ServiceProviderReferencedRepositoryInterface, SearchableRepositoryInterface
{
    use FindByAliasTrait,
        ServiceProviderReferencedRepositoryTrait;

    /**
     * @param array $pks
     * @return iterable
     */
    public function findMatchedLocation(array $pks): iterable
    {
        return $this->createQueryBuilder('t')
            ->select(
                'partial t.{id,country,destination,alias.alias,category}',
                'partial country.{id,isoCode,alias.alias}',
                'partial destination.{id,alias.alias}',
                'partial category.{id,simple}'
            )
            ->innerJoin('t.country', 'country')
            ->innerJoin('t.destination', 'destination')
            ->innerJoin('t.category', 'category')
            ->where('t.id in(:ids)')
            ->setParameter(':ids', $pks)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilderForFilterCriteria(): QueryBuilder
    {
        return $this->createQueryBuilder('t', 't.id')
            ->select('t, mainImage, country, destination, category')
            ->innerJoin('t.mainImage', 'mainImage')
            ->innerJoin('t.country', 'country')
            ->innerJoin('t.destination', 'destination')
            ->innerJoin('t.category', 'category')
            ->where('t.published = true');
    }


    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findCount()
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array $hotels
     */
    public function loadRelatedEntities(array $hotels)
    {
        $this->loadRelatedFacilities($hotels);
        $this->loadRelatedInterestPoints($hotels);
        $this->loadRelatedImage($hotels);
    }

    /**
     * @return mixed
     */
    public function findHotelsToUpdatePublishStatus()
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->leftJoin('t.images', 'images')
            ->leftJoin('t.rooms', 'rooms')
            ->where('t.published = true')
            ->andWhere(' rooms.id  is null OR images.id is null')
            ->groupBy('t.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $hotels
     */
    private function loadRelatedFacilities(array $hotels)
    {
        $this->createQueryBuilder('t')
            ->select('partial t.{id},  facilities, facility, facility_group')
            ->leftJoin('t.facilities', 'facilities')
            ->leftJoin('facilities.facility', 'facility')
            ->leftJoin('facility.group', 'facility_group')
            ->where('t in (:hotels)')
            ->andWhere( 'facilities.indYesOrNo is null or facilities.indYesOrNo = true ')
            ->setParameter('hotels', $hotels)
            ->getQuery()
            ->execute();
    }

    /**
     * @param array $hotels
     */
    private function loadRelatedInterestPoints(array $hotels)
    {
        $this->createQueryBuilder('t')
            ->select('partial t.{id},  interestPoints')
            ->leftJoin('t.interestPoints', 'interestPoints')
            ->where('t in (:hotels)')
            ->setParameter('hotels', $hotels)
            ->getQuery()
            ->execute();
    }

    /**
     * @param array $hotels
     */
    private function loadRelatedImage(array $hotels)
    {
        $this->createQueryBuilder('t')
            ->select('partial t.{id},  mainImage, resizedImages')
            ->leftJoin('t.mainImage', 'mainImage')
            ->leftJoin('mainImage.resizedImages', 'resizedImages')
            ->where('t in (:hotels)')
            ->setParameter('hotels', $hotels)
            ->getQuery()
            ->execute();
    }
}