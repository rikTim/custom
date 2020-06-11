<?php

namespace Apl\HotelsDbBundle\Repository\Location;


use Apl\HotelsDbBundle\Entity\Location\Country;
use Apl\HotelsDbBundle\Repository\Traits\FindByAliasTrait;
use Apl\HotelsDbBundle\Service\Search\SearchableRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryTrait;
use Doctrine\ORM\EntityRepository;

/**
 * Class DestinationRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Location
 */
class DestinationRepository extends EntityRepository implements ServiceProviderReferencedRepositoryInterface, SearchableRepositoryInterface
{
    use ServiceProviderReferencedRepositoryTrait,
        FindByAliasTrait;

    /**
     * @param array $pks
     * @return iterable
     */
    public function findMatchedLocation(array $pks): iterable
    {
        return $this->createQueryBuilder('t')
            ->select(
                'partial t.{id,country,rootDestination,alias.alias}',
                'partial country.{id,isoCode,alias.alias}',
                'partial rootDestination.{id,alias.alias}'
            )
            ->innerJoin('t.country', 'country')
            ->leftJoin('t.rootDestination', 'rootDestination')
            ->where('t.id in(:ids)')
            ->setParameter(':ids', $pks)
            ->getQuery()
            ->getResult();
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
     * @param Country $country
     * @return mixed
     */
    public function findByCountry(Country $country)
    {
        return $this->createQueryBuilder('t')
            ->where('t.country = :country')
            ->andWhere('t.rootDestination is null ')
            ->setParameter(':country', $country->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $destinations
     * @return array
     */
    public function findContainedDestinations(array $destinations): array
    {
        $response = $this->createQueryBuilder('t')
            ->select('t.id')
            ->innerJoin('t.containedInTheDestinations', 'containedInTheDestinations')
            ->where('containedInTheDestinations IN (:destination)')
            ->setParameter(':destination', $destinations)
            ->getQuery()
            ->getArrayResult()
        ;

        return array_column($response, 'id');
    }
}