<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\Criteria;


use Apl\HotelsDbBundle\Entity\Locale;

/**
 * Interface StaticDataCriteriaInterface
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\Criteria
 */
interface StaticDataCriteriaInterface
{
    /**
     * @return Locale
     */
    public function getLocale() : Locale;

    /**
     * @param Locale $locale
     * @return StaticDataCriteriaInterface
     */
    public function withLocale(Locale $locale): StaticDataCriteriaInterface;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedDateTime(): ?\DateTimeImmutable;

    /**
     * @param \DateTimeImmutable $updatedDateTime
     * @return StaticDataCriteriaInterface
     */
    public function withUpdatedDateTime(\DateTimeImmutable $updatedDateTime): StaticDataCriteriaInterface;

    /**
     * @return int
     */
    public function getBulkSizeLimit(): int;

    /**
     * @param int $bulkSizeLimit
     * @return StaticDataCriteriaInterface
     */
    public function withBulkSizeLimit(int $bulkSizeLimit): StaticDataCriteriaInterface;

    /**
     * @return int
     */
    public function getFirstPage(): int;

    /**
     * @param int $firstPage
     * @return StaticDataCriteriaInterface
     */
    public function withFirstPage(int $firstPage): StaticDataCriteriaInterface;
}