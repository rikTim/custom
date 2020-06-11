<?php

namespace Apl\HotelsDbBundle\Repository\Dictionary;


use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionRepositoryInterface;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class AccommodationVersionRepository extends EntityRepository implements EntityVersionRepositoryInterface
{
    use EntityVersionRepositoryTrait;
}