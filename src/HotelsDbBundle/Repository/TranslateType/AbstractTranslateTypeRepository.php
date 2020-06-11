<?php


namespace Apl\HotelsDbBundle\Repository\TranslateType;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * Class AbstractTranslateTypeRepository
 * @package Apl\HotelsDbBundle\Repository\TranslateType
 */
abstract class AbstractTranslateTypeRepository extends EntityRepository implements TranslateTypeRepositoryInterface
{
    /**
     * @param string $entityAlias
     * @param ProxyQueryInterface $proxyQuery
     * @param string $rootTableAlias
     * @param string $entityField
     * @param array $value
     * @return bool
     */
    public function setAdminFilter(
        string $entityAlias,
        ProxyQueryInterface $proxyQuery,
        string $rootTableAlias,
        string $entityField,
        array $value
    ): bool
    {
        $searchField = $this->getSearchFieldName();
        $translateTableAlias = "tr_{$entityField}";
        $entityAliasParameter = ":entityAlias_{$entityField}";
        $entityFieldParameter = ":entityField_{$entityField}";
        $valueParameter = ":value_{$entityField}";

        /** @var QueryBuilder $proxyQuery */
        $proxyQuery
            ->innerJoin(
                $this->getClassName(),
                $translateTableAlias, 'WITH',
                "{$translateTableAlias}.entityAlias = {$entityAliasParameter}
                    AND {$translateTableAlias}.entityId = {$rootTableAlias}.id
                    AND {$translateTableAlias}.entityField = {$entityFieldParameter}"
            )
            ->andWhere("{$translateTableAlias}.{$searchField} LIKE {$valueParameter}")
            ->setParameter($entityAliasParameter, $entityAlias)
            ->setParameter($entityFieldParameter, $entityField)
            ->setParameter($valueParameter, "%{$value['value']}%");

        return true;
    }

    /**
     * @return string
     */
    protected function getSearchFieldName(): string
    {
        return 'value';
    }

    /**
     * Метод поиска сущностей один для всех типов переводов, поэтому не вижу смысла выносить его в трейт
     *
     * @param string $entityAlias
     * @param array $entitiesIds
     * @param Locale|null $locale
     * @return iterable
     */
    public function findTranslate(string $entityAlias, array $entitiesIds, Locale $locale = null): iterable
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.entityAlias = :entityAlias AND t.entityId IN (:entitiesIds)')
            ->setParameter(':entityAlias', $entityAlias)
            ->setParameter(':entitiesIds', $entitiesIds);

        if ($locale) {
            $qb->andWhere('t.locale.locale = :locale')
                ->setParameter(':locale', (string)$locale);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param TranslateTypeInterface[] $translateTypes
     * @param int $limit
     * @param array $scoringCoefficients
     * @param bool $fullWordMatch
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function searchBestMatch(array $translateTypes, int $limit, array $scoringCoefficients, bool $fullWordMatch): array
    {
        if (!$translateTypes) {
            return [];
        }

        $metadata = $this->getClassMetadata();
        $tableName = $metadata->getTableName();
        $columnEntityAlias = $metadata->getColumnName('entityAlias');
        $columnEntityField = $metadata->getColumnName('entityField');
        $columnEntityId = $metadata->getColumnName('entityId');
        $columnSearch = $metadata->getColumnName($this->getSearchFieldName());

        $parameters = [':offsetEnd' => (int)max(1, $limit)];
        $subQueries = [];

        $searchPattern = $fullWordMatch ? ' +' : '* ';
        foreach ($translateTypes as $translateType) {
            $preparedTranslate = trim(mb_ereg_replace('[^\p{L}]+', $searchPattern, (string)$translateType), $searchPattern);

            if (!$preparedTranslate) {
                continue;
            }

            $preparedTranslate = '+' . $preparedTranslate . '*';

            $searchParameterName = ':search_' . count($subQueries);
            $searchParameterNameClean = ':search_clean_' . count($subQueries);
            $fieldParameterName = ':field_' . count($subQueries);
            $aliasParameterName = ':entityAlis_' . count($subQueries);

            $coefficient = max(1, (float)($scoringCoefficients[$translateType->getEntityAlias()] ?? 1));

            // В скоринге учитвается полное совпадение и отношение длинны запроса на длинну фактически найденного текста:
            // - полное совпадение с учетом регистра - коэффициент 4
            // - полное совпадение без учета регистра - коэффициент 3
            // - совпадение по длинне - коэффициент [0..2]
            $subQueries[] = "SELECT
                  {$columnEntityAlias} as entityAlias,
                  {$columnEntityId} as entityId,
                  {$searchParameterName} as matched,
                  {$columnSearch} as find,
                  MATCH({$columnSearch}) AGAINST({$searchParameterName} IN BOOLEAN MODE)
                      * (CASE WHEN {$columnSearch} = {$searchParameterNameClean}
                          THEN (CASE WHEN {$columnSearch} COLLATE 'utf8_bin' = {$searchParameterNameClean} COLLATE 'utf8_bin' THEN 4 ELSE 3 END)
                          ELSE ((LENGTH({$searchParameterNameClean})/LENGTH({$columnSearch})) * 2)
                      END) 
                      * {$coefficient}
                      as score
                FROM {$tableName}
                WHERE
                    MATCH({$columnSearch}) AGAINST({$searchParameterName} IN BOOLEAN MODE)
                    AND {$columnEntityAlias} = {$aliasParameterName}
                    AND {$columnEntityField} = {$fieldParameterName}
                ORDER BY `score` DESC
                LIMIT :offsetEnd";

            $parameters[$searchParameterName] = $preparedTranslate;
            $parameters[$searchParameterNameClean] = preg_replace('/\+|\*/m', '', $preparedTranslate);
            $parameters[$fieldParameterName] = $translateType->getEntityField();
            $parameters[$aliasParameterName] = $translateType->getEntityAlias();
        }

        if (!$subQueries) {
            return [];
        }

        $rsm = new ResultSetMapping();
        $rsm
            ->addIndexByScalar('entityId')
            ->addScalarResult('entityAlias', 'entityAlias')
            ->addScalarResult('entityId', 'entityId')
            ->addScalarResult('matched', 'matched')
            ->addScalarResult('find', 'find')
            ->addScalarResult('score', 'score');

        if (\count($subQueries) > 1) {
            $stm = $this->getEntityManager()->createNativeQuery(
                'SELECT entityAlias, entityId, GROUP_CONCAT(DISTINCT matched) as matched, GROUP_CONCAT(DISTINCT find) as find, SUM(score) AS score
                FROM (
                    (' . implode(') UNION ALL (', $subQueries) . ')
                ) AS unions
                GROUP BY entityAlias, entityId
                ORDER BY SUM(score) DESC
                LIMIT :offsetEnd',
                $rsm
            );
        } else {
            $stm = $this->getEntityManager()->createNativeQuery(current($subQueries), $rsm);
        }

        $stm->setParameters($parameters);
        return $stm->getResult();
    }


    /**
     * @param Locale $from
     * @param Locale $to
     * @return mixed
     */
    public function findNoTranslate(Locale $from ,Locale $to)
    {
        $metadata = $this->getClassMetadata();
        $tableName = $metadata->getTableName();
        $columnEntityAlias = $metadata->getColumnName('entityAlias');
        $columnEntityField = $metadata->getColumnName('entityField');
        $columnEntityId = $metadata->getColumnName('entityId');
        $columnLocale = $metadata->getColumnName('locale');
        $columnSearch = $metadata->getColumnName($this->getSearchFieldName());
        $fromLocale = $from->getLocale();
        $toLocale = $to->getLocale();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('entityAlias', 'entityAlias')
            ->addScalarResult('entityId', 'entityId')
            ->addScalarResult('locale', 'locale')
            ->addScalarResult('entityField', 'entityField')
            ->addScalarResult('search', 'search');

        $query = "SELECT f.{$columnEntityAlias} as entityAlias,
                    f.{$columnEntityId} as entityId,
                    f.{$columnLocale} as locale,
                    f.{$columnEntityField} as entityField, 
                    f.{$columnSearch} as search 
                     FROM {$tableName} f
                    left join {$tableName} t on  f.{$columnEntityAlias} = t.{$columnEntityAlias}  and t.{$columnLocale} = '{$toLocale}' and f.{$columnEntityId}  = t.{$columnEntityId}  
                    where f.{$columnLocale} = '{$fromLocale}' and t.id  is null and f.{$columnEntityAlias} not like '%Version%'";
        return $this->getEntityManager()->createNativeQuery($query, $rsm)->getResult();

    }


    /**
     * @param array $data
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     */
    public function insertNewTranslates(array $data): int
    {
        $metadata = $this->getClassMetadata();
        $tableName = $metadata->getTableName();
        $insert = [
            'entityAlias' => $metadata->getColumnName('entityAlias'),
            'entityId' => $metadata->getColumnName('entityId'),
            'locale' => $metadata->getColumnName('locale'),
            'entityField' => $metadata->getColumnName('entityField'),
            'search' => $metadata->getColumnName($this->getSearchFieldName()),
        ];



        $values = [];
        $date = new \DateTime();
        foreach (array_values($data) as $key => $item) {

            if (!array_diff_key($insert, $item)) {
                $values [] = '(\'' . implode('\',\'', array_merge(array_intersect_key($item, $insert), [$date->format('Y-m-d H:i:s'), $date->format('Y-m-d H:i:s')])).'\')';
            } else {
                throw new RuntimeException(sprintf('Not all required fields to save entity "%s"', $this->_entityName));
            }
        }

        if (($count = \count($values)) > 0) {
            $values = array_chunk($values, 1000);
            foreach ($values as $value) {
                $query = "INSERT INTO " .  $tableName;
                $query .= " ( `" . implode('`, `', $insert). "`,`created`,`updated`) VALUES " . implode(',', $value);

                $stm = $this->getEntityManager()->getConnection()->prepare($query);
                $stm->execute();
            }
        }
        return $count;
    }

}