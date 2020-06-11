<?php


namespace Apl\HotelsDbBundle\Repository\Location;


use Apl\HotelsDbBundle\Repository\Traits\FindByAliasTrait;
use Apl\HotelsDbBundle\Service\Search\SearchableRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryTrait;
use Doctrine\ORM\EntityRepository;

/**
 * Class CountryRepository
 * @package Apl\HotelsDbBundle\Repository\Location
 */
class CountryRepository extends EntityRepository implements ServiceProviderReferencedRepositoryInterface, SearchableRepositoryInterface
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
            ->select('partial t.{id,isoCode,alias.alias}')
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
}