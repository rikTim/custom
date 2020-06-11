<?php


namespace Apl\HotelsDbBundle\Repository\Hotel;


use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedRepositoryTrait;
use Doctrine\ORM\EntityRepository;

class HotelRoomRepository extends EntityRepository implements ServiceProviderReferencedRepositoryInterface
{
    use ServiceProviderReferencedRepositoryTrait;
}