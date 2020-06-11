<?php


namespace Apl\HotelsDbBundle\Service;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Trait EventDispatcherAwareTrait
 *
 * @package Apl\HotelsDbBundle\Service
 */
trait EventDispatcherAwareTrait
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}