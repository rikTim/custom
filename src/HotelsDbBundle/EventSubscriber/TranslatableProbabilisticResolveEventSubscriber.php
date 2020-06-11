<?php


namespace Base\HotelsDbBundle\EventSubscriber;


use Base\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Base\HotelsDbBundle\Service\ServiceProvider\Event\ProbabilisticResolveEvent;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Base\HotelsDbBundle\Service\ObjectTranslator\TranslateSearch;
use Base\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TranslatableProbabilisticResolveEventSubscriber implements EventSubscriberInterface
{
    use EntityManagerAwareTrait;

    /**
     * @var TranslateSearch
     */
    private $translateSearch;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(TranslateSearch $translateSearch, LoggerInterface $logger)
    {
        $this->translateSearch = $translateSearch;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProbabilisticResolveEvent::NAME => 'onProbabilisticResolve',
        ];
    }

    /**
     * @param ProbabilisticResolveEvent $event
     */
    public function onProbabilisticResolve(ProbabilisticResolveEvent $event): void
    {
        $entityClassName = $event->getEntityClassName();
        if (
            !is_subclass_of($entityClassName, TranslatableObjectInterface::class)
            || !\call_user_func("{$entityClassName}::isAllowProbabilisticResolve")
        ) {
            return;
        }

        $translateObjectAlias = \call_user_func("{$entityClassName}::getTranslateAlias");
        foreach ($event->getMappingData() as $reference => $entityData) {
            if ($entityData->getTranslatableFields()) {
                $translateTypeCollection = [];
                foreach ($entityData->getTranslatableFields() as $translateType) {
                    $translateTypeCollection[] = (clone $translateType)->setEntityAlias($translateObjectAlias);
                }

                $searchResult = $this->translateSearch->searchBestMatch($translateTypeCollection, 1, [], true);
                if ($searchResult) {
                    $bestMatch = current($searchResult);
                    /** @var ServiceProviderReferencedEntityInterface $entity */
                    $entity = $this->entityManager->getRepository($entityClassName)->find($bestMatch['entityId']);
                    if (!$entity->getReferenceToServiceProvider($event->getServiceProviderAlias())) {
                        $this->logger->info('Probabilistic resolved', ['reference' => $reference] + $bestMatch);
                        $event->resolve($reference, $entity);
                    }

                }
            }
        }
    }
}