<?php

namespace Apl\HotelsDbBundle\Repository\Dictionary;


use Apl\HotelsDbBundle\Repository\Traits\FindByAliasTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryTrait;
use Doctrine\ORM\EntityRepository;

/**
 * Class CurrencyRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Dictionary
 */
class CurrencyRepository extends EntityRepository implements ServiceProviderReferencedRepositoryInterface
{
    use ServiceProviderReferencedRepositoryTrait;
}
