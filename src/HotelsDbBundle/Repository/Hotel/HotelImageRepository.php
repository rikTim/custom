<?php


namespace Apl\HotelsDbBundle\Repository\Hotel;


use Apl\HotelsDbBundle\Entity\Hotel\HotelImage;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class HotelFacilityRepository
 * @package Apl\HotelsDbBundle\Repository\Hotel
 */
class HotelImageRepository extends EntityRepository implements ServiceProviderReferencedRepositoryInterface
{
    use ServiceProviderReferencedRepositoryTrait;

    /**
     * @param int $perPage
     * @param null $total
     * @return \Generator|HotelImage[]
     */
    public function notResizedGenerator(int $perPage = 100, &$total = null): \Generator
    {
        $query = $this->createQueryBuilder('t')
            ->addSelect('resizedImages')
            ->leftJoin('t.resizedImages', 'resizedImages')
            ->where('t.resized IS NULL')
            ->setFirstResult(0)
            ->setMaxResults($perPage)
            ->getQuery();

        $paginator = new Paginator($query);
        $total = \count($paginator);

        for ($i = 0; $i < $total; $i += $perPage) {
            yield from $paginator;
        }
    }
}