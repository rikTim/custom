<?php


namespace Apl\HotelsDbBundle\Service\Search;


/**
 * Interface SearchIndexEntityInterface
 *
 * @package Apl\HotelsDbBundle\Service\Search
 */
interface SearchIndexEntityInterface
{
    /**
     * @return string
     */
    public function getEntityClassName(): string;

    /**
     * @return int
     */
    public function getEntityId(): int;
}