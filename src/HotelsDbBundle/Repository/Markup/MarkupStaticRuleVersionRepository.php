<?php


namespace Apl\HotelsDbBundle\Repository\Markup;



use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionRepositoryInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionRepositoryTrait;
use Doctrine\ORM\EntityRepository;

/**
 * Class MarkupStaticRuleVersionRepository
 *
 * @package Apl\HotelsDbBundle\Repository\Markup
 */
class MarkupStaticRuleVersionRepository extends EntityRepository implements EntityVersionRepositoryInterface
{
    use EntityVersionRepositoryTrait;
}