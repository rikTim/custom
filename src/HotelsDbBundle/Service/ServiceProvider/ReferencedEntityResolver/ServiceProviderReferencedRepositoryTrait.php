<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Trait ServiceProviderReferencedRepositoryTrait
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver
 */
trait ServiceProviderReferencedRepositoryTrait
{
    /**
     * @return ServiceProviderReferenceInterface
     */
    public function createServiceProviderReference(): ServiceProviderReferenceInterface
    {
        $referenceClassName = $this->getClassMetadata()->getAssociationTargetClass('serviceProviderReferences');
        return new $referenceClassName;
    }

    /**
     * Implemented in Doctrine\ORM\EntityRepository
     * @return ClassMetadata
     */
    abstract protected function getClassMetaData();

    /**
     * @param ServiceProviderAlias $alias
     * @param array $references
     * @return array
     */
    public function resolveAllByServiceProviderReference(ServiceProviderAlias $alias, array $references): array
    {
        $result = $this->createQueryBuilder('t')
            ->setCacheable(false)
            ->select('sp.reference, t')
            ->innerJoin('t.serviceProviderReferences', 'sp')
            ->where('sp.alias.alias = :alias')
            ->andWhere('sp.reference IN (:references)')
            ->setParameters([
                'alias' => (string)$alias,
                'references' => \array_map('\strval', $references)
            ])
            ->getQuery()
            ->getResult();

        return $result ? array_column($result, 0, 'reference') : $result;
    }

    /**
     * Implemented in Doctrine\ORM\EntityRepository
     * @param string $alias
     * @param string $indexBy
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);

    /**
     * Simple search equals entities
     *
     * @param array $fields
     * @param ServiceProviderReferencedEntityInterface|null $parentEntity
     * @return ServiceProviderReferencedEntityInterface|object|null
     */
    public function resolveByServiceProviderData(
        array $fields,
        ?ServiceProviderReferencedEntityInterface $parentEntity = null
    ): ?ServiceProviderReferencedEntityInterface
    {
        $filteredData = array_filter(array_intersect_key($fields, array_flip($this->getFiledNamesToMatching())));
        if (!$filteredData) {
            return null;
        }

        $qb = $this->createQueryBuilder('e');

        if ($parentEntity) {
            $this->attachParentRelation($qb, $parentEntity);
        }

        foreach ($filteredData as $field => $data) {
            $qb->andWhere($qb->expr()->eq('e.' . $field, ':' . $field));
            $qb->setParameter($field, $data);
        }

        try {
            return $qb->setMaxResults(2)->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * Этот метод можно переопределить для того, что бы явно указать поля по которым можно искать полные совпадения
     * По-умолчанию проверяются все поля, кроме первичных ключей
     *
     * @return array
     */
    protected function getFiledNamesToMatching(): array
    {
        $classMetaData = $this->getClassMetaData();
        return array_values(array_diff($classMetaData->getFieldNames(), $classMetaData->getIdentifierFieldNames()));
    }

    /**
     * @param QueryBuilder $qb
     * @param ServiceProviderReferencedEntityInterface $parentEntity
     */
    protected function attachParentRelation(QueryBuilder $qb, ServiceProviderReferencedEntityInterface $parentEntity): void
    {
        $parentClass = ClassUtils::getRealClass(\get_class($parentEntity));

        $metaData = $this->getClassMetaData();
        $association = null;
        foreach ($metaData->getAssociationNames() as $associationName) {
            if ($parentClass === $metaData->getAssociationTargetClass($associationName)) {
                if ($association) {
                    return;
                }

                $association = $associationName;
            }
        }

        if (!$association) {
            return;
        }

        $qb->andWhere($qb->expr()->eq('e.' . $association, ':_parent_' . $association));
        $qb->setParameter(':_parent_' . $association, $parentEntity);
    }
}