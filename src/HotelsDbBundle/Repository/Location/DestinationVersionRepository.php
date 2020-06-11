<?php


namespace Apl\HotelsDbBundle\Repository\Location;


use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionRepositoryInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class DestinationVersionRepository extends EntityRepository implements EntityVersionRepositoryInterface
{
    use EntityVersionRepositoryTrait;
}