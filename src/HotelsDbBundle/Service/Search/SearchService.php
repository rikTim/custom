<?php

namespace Apl\HotelsDbBundle\Service\Search;


use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Location\Country;
use Apl\HotelsDbBundle\Entity\Location\Destination;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateSearch;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;


/**
 * Class SearchService
 * @package Apl\HotelsDbBundle\Service\Search
 */
class SearchService
{
    use EntityManagerAwareTrait;

    /**
     * @param string $indexClassName
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchByIndex(string $indexClassName, string $query, int $limit = 10): array
    {
        $repository = $this->entityManager->getRepository($indexClassName);
        if (!$repository instanceof SearchIndexRepositoryInterface) {
            throw new RuntimeException(sprintf('Entity "%s" is not search index', $indexClassName));

        }

        $bestMatch = $repository->search($query, $limit);

        // Aggregation by entity
        $searchResultGroups = [];
        foreach ($bestMatch as $position => $match) {
            $searchResultGroups[$match->getEntityClassName()][$match->getEntityId()] = $position;
        }

        // Find entities
        $resultSortedCollection = [];
        foreach ($searchResultGroups as $className => $aggregated) {
            $repository = $this->entityManager->getRepository($className);
            if (!($repository instanceof SearchableRepositoryInterface)) {
                throw new RuntimeException('Incorrect repository');
            }

            $entities = $repository->findMatchedLocation(array_keys($aggregated));
            foreach ($entities as $entity) {
                $resultSortedCollection[$aggregated[$entity->getId()]] = $entity;
            }
        }

        ksort($resultSortedCollection);

        return $resultSortedCollection;
    }
}