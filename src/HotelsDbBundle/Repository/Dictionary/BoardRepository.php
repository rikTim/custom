<?php

namespace Apl\HotelsDbBundle\Repository\Dictionary;


use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class BoardRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Dictionary
 */
class BoardRepository extends EntityRepository implements ServiceProviderReferencedRepositoryInterface
{
    use ServiceProviderReferencedRepositoryTrait,
        LoggerAwareTrait;

    public const ROOM_ONLY_CODE = 'RO';

    /**
     * @return int|null
     */
    public function findRoomOnlyId(): ?int
    {
        try {
            return $this->createQueryBuilder('b')
                ->select('b.id')
                ->where('b.code = :code')
                ->setParameter('code', self::ROOM_ONLY_CODE)
                ->setCacheable(true)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $this->logger->warning('In database exist multiply "room only" boards');
            return null;
        }
    }
}
