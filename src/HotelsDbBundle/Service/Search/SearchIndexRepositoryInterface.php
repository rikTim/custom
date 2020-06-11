<?php


namespace Apl\HotelsDbBundle\Service\Search;


/**
 * Interface SearchIndexRepositoryInterface
 *
 * @package Apl\HotelsDbBundle\Service\Search
 */
interface SearchIndexRepositoryInterface
{
    public function updateSearchIndex(): void;

    /**
     * @param string $query
     * @param int $limit
     * @return SearchIndexEntityInterface[]
     */
    public function search(string $query, int $limit): array;
}