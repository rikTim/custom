<?php


namespace Apl\HotelsDbBundle\Service;


use Symfony\Component\Lock\Factory;

/**
 * Trait LockFactoryAwareTrait
 *
 * @package Apl\HotelsDbBundle\Service
 */
trait LockFactoryAwareTrait
{
    /**
     * @var Factory
     */
    private $lockFactory;

    /**
     * @param Factory $lockFactory
     * @required
     */
    public function setLockFactory(Factory $lockFactory): void
    {
        $this->lockFactory = $lockFactory;
    }
}