<?php


namespace Apl\HotelsDbBundle\Repository\Money;


use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\Money\PriceControl;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Doctrine\ORM\EntityRepository;

class PriceControlRepository extends AbstractPriceRepository
{

    /**
     * @param string $sort
     * @param Board|null $board
     * @return mixed
     */
    public function findAllBySort(string $sort = 'DESC', Board $board = null)
    {
        $qb = $this->createQueryBuilder('p');
        if ($board instanceof Board) {
            $qb->where('p.board = :board')
                ->setParameter('board', $board->getId());
        }
        $qb->orderBy('p.price.amount', $sort);
        $query = $qb->getQuery();
        return $query->execute();
    }

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
            'adults' => 'adults',
            'children' => 'children',
            'provider_alias' => 'provider_alias',
            'amount' => 'amount',
            'currency' => 'currency'
        ];

        $update = ['amount','currency','provider_alias','update'];
        $table = $this->getClassMetadata()->getTableName();
        return parent::insertOnDuplicateUpdate($data, $table, $insert, $update);
    }
}