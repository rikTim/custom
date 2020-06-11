<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\Criteria;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;

/**
 * Class StaticDataCriteria
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\Criteria
 */
class StaticDataCriteria implements StaticDataCriteriaInterface
{
    /**
     * @var \DateTimeImmutable
     */
    private $updateDateTime;

    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var int
     */
    private $bulkSizeLimit;

    /**
     * @var int
     */
    private $firstPage = 0;

    /**
     * @return Locale
     */
    public function getLocale() : Locale
    {
        return $this->locale;
    }

    /**
     * @param Locale $locale
     * @return $this
     */
    public function withLocale(Locale $locale): StaticDataCriteriaInterface
    {
        $new = clone $this;
        $new->locale = $locale;
        return $new;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedDateTime(): ?\DateTimeImmutable
    {
        return $this->updateDateTime;
    }

    /**
     * @param \DateTimeImmutable $updatedDateTime
     * @return $this
     */
    public function withUpdatedDateTime(?\DateTimeImmutable $updatedDateTime): StaticDataCriteriaInterface
    {
        if ($updatedDateTime === $this->updateDateTime) {
            return $this;
        }

        $new = clone $this;
        $new->updateDateTime = $updatedDateTime;
        return $new;
    }

    /**
     * @return int
     */
    public function getBulkSizeLimit(): int
    {
        return $this->bulkSizeLimit;
    }

    /**
     * @param int $bulkSizeLimit
     * @return StaticDataCriteriaInterface
     * @throws \Apl\HotelsDbBundle\Exception\InvalidArgumentException
     */
    public function withBulkSizeLimit(int $bulkSizeLimit): StaticDataCriteriaInterface
    {
        if ($bulkSizeLimit <= 0) {
            throw new InvalidArgumentException('Bulk size limit must be more than 0');
        }

        $new = clone $this;
        $new->bulkSizeLimit = $bulkSizeLimit;
        return $new;
    }

    /**
     * @return int
     */
    public function getFirstPage(): int
    {
        return $this->firstPage ?? 0;
    }

    /**
     * @param int $firstPage
     * @return StaticDataCriteriaInterface
     */
    public function withFirstPage(int $firstPage): StaticDataCriteriaInterface
    {
        if ($firstPage < 0) {
            throw new InvalidArgumentException('First page must be more or equal 0');
        }

        $new = clone $this;
        $new->firstPage = $firstPage;
        return $new;
    }
}