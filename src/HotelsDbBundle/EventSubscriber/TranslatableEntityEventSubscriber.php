<?php


namespace Apl\HotelsDbBundle\EventSubscriber;


use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateManager;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Class TranslatableEntityEventSubscriber
 * @package Apl\HotelsDbBundle\EventSubscriber
 */
class TranslatableEntityEventSubscriber implements EventSubscriber
{
    /**
     * @var TranslateManager
     */
    private $translateManager;

    /**
     * TranslatableEntityEventSubscriber constructor.
     * @param TranslateManager $translateManager
     */
    public function __construct(TranslateManager $translateManager)
    {
        $this->translateManager = $translateManager;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
            Events::preRemove,
        ];
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof TranslatableObjectInterface) {
            $this->translateManager->attachCollection($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof TranslatableObjectInterface) {
            $this->translateManager->removeTranslations($entity);
        }
    }
}