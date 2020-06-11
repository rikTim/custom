<?php


namespace Apl\HotelsDbBundle\Repository\Money;


use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Money\Price\MinimumPrice;
use Apl\HotelsDbBundle\Repository\Traits\FindExistByUniqueConstraintTrait;
use Doctrine\ORM\EntityRepository;

/**
 * Class MinimumPriceRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Money
 */
class MinimumPriceRepository extends EntityRepository
{
    use FindExistByUniqueConstraintTrait;

    /**
     * @param Hotel[] $hotels
     * @param \DateTimeImmutable $startDate
     * @param \DateTimeImmutable $endDate
     * @param int $adults
     * @param int $children
     * @param int[] $boardIds list of board ids
     * @return MinimumPrice[]|array
     */
    public function findMinimumPricesForHotelList(
        array $hotels,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        int $adults,
        int $children,
        array $boardIds = []
    ): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.hotel IN (:hotels) AND p.adults = :adults AND p.children = :children
                AND (
                    (p.roomQuantity = 0 AND p.checkIn = :startDate AND p.checkOut = :endDate)
                    OR (p.roomQuantity > 0 AND p.checkIn between :startDate AND :endDateCheckIn)
                )')
            ->setParameters([
                'hotels' => $hotels,
                'startDate' => $startDate,
                'endDateCheckIn' => $endDate->modify('-1 day'),
                'endDate' => $endDate,
                'adults' => $adults,
                'children' => $children,
            ]);

        if ($boardIds) {
            $qb->innerJoin('p.board', 'board');
            $qb->andWhere('board.id IN (:board)')
                ->setParameter('board', $boardIds);
        }

        return array_reduce($qb->getQuery()->getResult(), function (array $acc, MinimumPrice $entity) {
            $acc[$entity->getHotel()->getId()][] = $entity;
            return $acc;
        }, []);
    }


    public function deleteToDateUpdate(\DateTime $to_date)
    {
        return $this->createQueryBuilder( 'r')
            ->delete()
            ->where( 'r.updated < :to_date')
            ->setParameters(['to_date'=> $to_date])
            ->getQuery()
            ->getResult();
    }
}