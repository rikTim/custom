<?php


namespace Apl\HotelsDbBundle\Service\EntityManagerProxy;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerAwareTrait
{
    /**
     * @var EntityManagerInterface|EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManagerProxy $entityManager
     * @required
     */
    public function setEntityManager(EntityManagerProxy $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

}