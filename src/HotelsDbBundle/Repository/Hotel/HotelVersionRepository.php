<?php


namespace Apl\HotelsDbBundle\Repository\Hotel;


use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionRepositoryInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class HotelVersionRepository extends EntityRepository implements EntityVersionRepositoryInterface
{
    use EntityVersionRepositoryTrait;
}