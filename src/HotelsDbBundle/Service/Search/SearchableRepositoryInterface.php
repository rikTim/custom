<?php


namespace Apl\HotelsDbBundle\Service\Search;


use Doctrine\Common\Collections\Collection;

/**
 * Interface SearchableRepositoryInterface
 * @package Apl\HotelsDbBundle\Service\Search
 */
interface SearchableRepositoryInterface
{
    /**
     * @param array $pks
     * @return Collection|array|iterable
     */
    public function findMatchedLocation(array $pks): iterable;
}