<?php


namespace Apl\HotelsDbBundle\Repository\SearchIndex;


use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Location\Country;
use Apl\HotelsDbBundle\Entity\Location\Destination;
use Apl\HotelsDbBundle\Service\Search\SearchIndexEntityInterface;
use Apl\HotelsDbBundle\Service\Search\SearchIndexRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Class LocationSearchIndexRepository
 *
 * @package Apl\HotelsDbBundle\Repository\SearchIndex
 */
class LocationSearchIndexRepository extends EntityRepository implements SearchIndexRepositoryInterface
{
    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateSearchIndex(): void
    {
        $tableNameOrig = $this->getClassMetadata()->getTableName();
        $tableNameNew = $tableNameOrig . '_new';
        $tableNameTmp = $tableNameOrig . '_tmp';

        $connection = $this->getEntityManager()->getConnection();

        $connection->executeQuery("DROP TABLE IF EXISTS {$tableNameNew}");
        $connection->executeQuery("CREATE TABLE {$tableNameNew} LIKE {$tableNameOrig}");

        $entities = [
            $connection->quote(Country::getTranslateAlias()) => 3,
            $connection->quote(Destination::getTranslateAlias()) => 2,
            $connection->quote(Hotel::getTranslateAlias()) => 1,
        ];

        $connection->executeQuery(
            "INSERT INTO {$tableNameNew} (`index_data`, `entity_class_name`, `entity_id`, `locale`, `score`)
                SELECT `nominative` as index_data, `entity_alias`, `entity_id`, `locale`,
                  CASE `entity_alias`"
            . implode(' ', array_map(function(string $alias, float $score) {
                return "WHEN {$alias} THEN {$score}";
            }, array_keys($entities), $entities)).
            " ELSE 1 END as `score`
                FROM hotels_db_translatable_string
                WHERE `entity_alias` IN (" . implode(',', array_keys($entities)) . ")
                AND entity_field = 'name'
               ON DUPLICATE KEY UPDATE `index_data` = VALUES(`index_data`)"
        );

        $connection->executeQuery("DROP TABLE IF EXISTS {$tableNameTmp}");
        $connection->executeQuery("RENAME TABLE {$tableNameOrig} to {$tableNameTmp}, {$tableNameNew} to {$tableNameOrig}");
        $connection->executeQuery("DROP TABLE IF EXISTS {$tableNameTmp}");
    }

    /**
     * @param string $query
     * @param int $limit
     * @return SearchIndexEntityInterface[]
     */
    public function search(string $query, int $limit): array
    {
        $searchPattern = '* ';
        $preparedQuery = trim(mb_ereg_replace('[^\p{L}]+', $searchPattern, $query), $searchPattern);
        if (!$preparedQuery) {
            return [];
        }

        $preparedQuery = '+' . $preparedQuery . '*';

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata($this->getClassName(), 't');

        $nq = $this->getEntityManager()->createNativeQuery(
            "SELECT " . $rsm->generateSelectClause() . "
                FROM {$this->getClassMetadata()->getTableName()} t
                WHERE
                    MATCH(`index_data`) AGAINST(:query IN BOOLEAN MODE)
                ORDER BY 
                 MATCH(`index_data`) AGAINST(:query IN BOOLEAN MODE)
                      * (CASE WHEN `index_data` = :queryClean
                          THEN (CASE WHEN `index_data` COLLATE 'utf8_bin' = :queryClean COLLATE 'utf8_bin' THEN 4 ELSE 3 END)
                          ELSE ((LENGTH(:queryClean)/LENGTH(`index_data`)) * 2)
                      END)
                      * `score` 
                  DESC
                LIMIT :offsetEnd",
            $rsm
        );

        $nq->setParameter('query', $preparedQuery);
        $nq->setParameter('queryClean', str_replace("\*", '', $preparedQuery));
        $nq->setParameter('offsetEnd', $limit);

        return $nq->getResult();
    }
}