<?php


namespace Apl\HotelsDbBundle\Repository\Money;


use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\Money\PriceCheckpoint;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Doctrine\ORM\EntityRepository;

/**
 * Class PriceCheckpointRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Money
 */
class PriceCheckpointRepository extends AbstractPriceRepository
{
    /**
     * @param array $data
     * @return int
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insert(array $data): int
    {
        $insert = [
            'hotel' => 'hotel_id',
            'board' => 'board_id',
            'amount' => 'amount',
            'currency' => 'currency'
        ];

        $update = ['amount','currency','update'];
        $table = $this->getClassMetadata()->getTableName();
        return parent::insertOnDuplicateUpdate($data, $table, $insert, $update);
    }

    /**
     * @param array $hotel_ids
     * @param Board $board
     * @return array
     */
    public function findAllByFilter(array $hotel_ids, Board $board): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.hotel IN(:hotel_ids) AND p.board = :board');
        $qb->setParameters(['board' => $board->getId(), 'hotel_ids' => array_values($hotel_ids)]);
        $query = $qb->getQuery();
        return $query->execute();

    }
}