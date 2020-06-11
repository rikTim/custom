<?php

namespace Apl\HotelsDbBundle\Repository\Dictionary;


use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryTrait;
use Doctrine\ORM\EntityRepository;

/**
 * Class CategoryRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Dictionary
 */
class CategoryRepository extends EntityRepository implements ServiceProviderReferencedRepositoryInterface
{
    use ServiceProviderReferencedRepositoryTrait;

    /**
     * @return array
     */
    public function findUniqueSimple(): array
    {
        return $this->createQueryBuilder('c')
            ->select('distinct c.simple')
            ->getQuery()
            ->getResult();
    }
}
