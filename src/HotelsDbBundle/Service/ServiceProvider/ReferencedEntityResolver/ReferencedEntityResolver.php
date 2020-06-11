<?php

namespace Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\Exception\EntityResolveMethodNotAllowedException;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\Event\ProbabilisticResolveEvent;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class ReferencedEntityResolver
 *
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver
 */
class ReferencedEntityResolver
{
    use EntityManagerAwareTrait,
        LoggerAwareTrait;

    private $resolveOnlyNewEntity;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var object[]
     */
    private $selfReferences;

    /**
     * ReferencedEntityResolver constructor.
     *
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->clearSelfReferences();
    }

    public function clearSelfReferences(): void
    {
        $this->selfReferences = [];
    }

    /**
     * @param bool $resolveOnlyNewEntity
     */
    public function setResolveOnlyNewEntity(bool $resolveOnlyNewEntity): void
    {
        $this->resolveOnlyNewEntity = $resolveOnlyNewEntity;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function isNewEntity($entity): bool
    {
        return !$this->entityManager->getUnitOfWork()->isInIdentityMap($entity);
    }

    /**
     * @param string $entityClassName
     * @param ServiceProviderAlias $serviceProviderAlias
     * @param ImportDTO[] $mappingData
     * @param ServiceProviderReferencedEntityInterface $parentEntity
     * @return \SplObjectStorage
     */
    public function resolve(
        string $entityClassName,
        ServiceProviderAlias $serviceProviderAlias,
        array $mappingData,
        ?ServiceProviderReferencedEntityInterface $parentEntity = null
    ): \SplObjectStorage
    {
        if ($entityClassName instanceof ServiceProviderReferencedEntityInterface) {
            throw new EntityResolveMethodNotAllowedException(
                sprintf('Entity with name "%s" not allowed mapping with different service providers', $entityClassName)
            );
        }

        $repository = $this->getRepository($entityClassName);

        $resolvedEntities = new \SplObjectStorage();
        // Already mapped entities
        if (\count($mappingData)) {
            if (!$parentEntity || !$this->isNewEntity($parentEntity)) {
                // Try find exist mapping
                $alreadyMapped = $repository->resolveAllByServiceProviderReference($serviceProviderAlias, array_keys($mappingData));
                foreach ($alreadyMapped as $reference => $entityMapping) {
                    if ($this->resolveOnlyNewEntity !== true) {
                        $resolvedEntities->attach($entityMapping, $mappingData[$reference]);
                    }

                    unset($mappingData[$reference]);
                }

                // Try resolve by full match data with existing entities
                foreach ($mappingData as $reference => $entityData) {
                    if ($entity = $repository->resolveByServiceProviderData($entityData->getFields(), $parentEntity)) {
                        if ($entity->getReferenceToServiceProvider($serviceProviderAlias)) {
                            $this->logger->debug('Incorrect full match resolve', ['reference' => $reference, 'resolved' => $entity->getReferenceToServiceProvider($serviceProviderAlias)->getReference()]);
                        } else {
                            $entity->getServiceProviderReferences()->add(
                                $repository->createServiceProviderReference()
                                    ->setType(ServiceProviderReferenceInterface::MAPPING_TYPE_AUTO_FULL_MATCH)
                                    ->setAlias($serviceProviderAlias)
                                    ->setReference($reference)
                                    ->setEntity($entity)
                            );

                            $resolvedEntities->attach($entity, $entityData);
                            unset($mappingData[$reference]);
                        }
                    }
                }

                // Rise event for try resolve by something specific service
                if (\count($mappingData)) {
                    $event = new ProbabilisticResolveEvent($serviceProviderAlias, $entityClassName, $mappingData);
                    $this->eventDispatcher->dispatch($event::NAME, $event);
                    foreach ($event->getResolved() as $reference => $entity) {
                        if ($entity->getReferenceToServiceProvider($serviceProviderAlias)) {
                            $this->logger->debug('Incorrect probabilistic resolve', ['reference' => $reference, 'resolved' => $entity->getReferenceToServiceProvider($serviceProviderAlias)->getReference()]);
                        } else {
                            $entity->getServiceProviderReferences()->add(
                                $repository->createServiceProviderReference()
                                    ->setType(ServiceProviderReferenceInterface::MAPPING_TYPE_AUTO_PROBABILISTIC)
                                    ->setAlias($serviceProviderAlias)
                                    ->setReference($reference)
                                    ->setEntity($entity)
                            );

                            $resolvedEntities->attach($entity, $mappingData[$reference]);
                            unset($mappingData[$reference]);
                        }
                    }
                }
            }

            // Create new entities
            foreach ($mappingData as $reference => $entityData) {
                /** @var ServiceProviderReferencedEntityInterface $entity */
                $entity = new $entityClassName;
                $entity->getServiceProviderReferences()->add(
                    $repository->createServiceProviderReference()
                        ->setType(ServiceProviderReferenceInterface::MAPPING_TYPE_PARENTAL)
                        ->setAlias($serviceProviderAlias)
                        ->setReference($reference)
                        ->setEntity($entity)
                );

                $resolvedEntities->attach($entity, $entityData);
            }

            // Attach self-referenced entities
            $externalReferences = [];
            foreach ($resolvedEntities as $entity) {
                $externalReferences[] = $resolvedEntities[$entity];
                foreach ($entity->getServiceProviderReferences() as $reference) {
                    $this->selfReferences[(string)$reference->getAlias()][$reference->getReference()] = $entity;
                }
            }

            // Resolve external references
            $this->resolveExternalReferences($entityClassName, $externalReferences, $serviceProviderAlias);
        }

        return $resolvedEntities;
    }

    /**
     * @param string $entityClassName
     * @param ImportDTO[] $resolvedEntities
     * @param ServiceProviderAlias $serviceProviderAlias
     */
    public function resolveExternalReferences(string $entityClassName, array $resolvedEntities, ServiceProviderAlias $serviceProviderAlias): void
    {
        $mappedEntities = [];
        $allowNullEntities = [];
        foreach ($this->entityManager->getClassMetadata($entityClassName)->getAssociationMappings() as $field => $mapping) {
            if (is_subclass_of($mapping['targetEntity'], ServiceProviderReferencedEntityInterface::class)) {
                $mappedEntities[$field] = $mapping['targetEntity'];

                $allowNull = true;

                // Allow null when join with joinTable or field in table allow null
                if (isset($mapping['joinColumns']) && \is_array($mapping['joinColumns'])) {
                    foreach ($mapping['joinColumns'] as $joinColumn) {
                        if (!$joinColumn['nullable']) {
                            $allowNull = false;
                            break;
                        }
                    }
                }

                if ($allowNull) {
                    $allowNullEntities[] = $mapping['targetEntity'];
                }
            }
        }

        $referencesRoot = (string)$serviceProviderAlias;

        $referenceMapping = [];
        /** @var ServiceProviderReferencedEntityInterface $entity */
        foreach ($resolvedEntities as $importDTO) {
            foreach ($importDTO->getFields() as $fieldName => $data) {
                if ($data && isset($mappedEntities[$fieldName])) {
                    if (\is_scalar($data)) {
                        $referenceMapping[$mappedEntities[$fieldName]][$data][$fieldName][] = $importDTO;
                    } elseif (\is_array($data)) {
                        foreach ($data as $reference) {
                            if (\is_scalar($reference)) {
                                $referenceMapping[$mappedEntities[$fieldName]][$reference][$fieldName][] = $importDTO;
                            }
                        }
                    }
                }
            }
        }

        foreach ($referenceMapping as $referenceClassName => $references) {
            $references = array_keys($references);
            $resolved = [];
            if ($referenceClassName === $entityClassName) {
                foreach ($references as $index => $reference) {
                    if (isset($this->selfReferences[$referencesRoot][$reference])) {
                        $resolved[$reference] = $this->selfReferences[$referencesRoot][$reference];
                        unset($references[$index]);
                    }
                }
            }

            if ($references) {
                foreach ($this->getRepository($referenceClassName)->resolveAllByServiceProviderReference($serviceProviderAlias, $references) as $reference => $entity) {
                    if (isset($resolved[$reference])) {
                        throw new RuntimeException(
                            sprintf('Cannot resolve related entity "%s" - duplicate resolve external references: %s', $referenceClassName, $reference)
                        );
                    }

                    $resolved[$reference] = $entity;
                }
            }

            if (\count($referenceMapping[$referenceClassName]) !== \count($resolved)) {
                $unresolved = \array_diff(\array_keys($referenceMapping[$referenceClassName]), \array_keys($resolved));
                if (!\in_array($referenceClassName, $allowNullEntities, true)) {
                    throw new RuntimeException(
                        sprintf('Cannot resolve related entity "%s" with external references: %s', $referenceClassName, implode(', ', $unresolved))
                    );
                }

                $this->logger->warning('Cannot resolve external references, but field nullable: ' . implode(', ', $unresolved));
                foreach ($unresolved as $reference) {
                    $resolved[$reference] = null;
                }
            }

            foreach ($resolved as $resolvedReference => $referenceEntity) {
                foreach ($referenceMapping[$referenceClassName][$resolvedReference] as $fieldName => $importDtoCollection) {
                    /** @var ImportDTO $importDTO */
                    foreach ($importDtoCollection as $importDTO) {
                        $importDTO->resolveReference($fieldName, $resolvedReference, $referenceEntity);
                    }
                }
            }
        }
    }

    /**
     * @param string $entityClassName
     * @return ServiceProviderReferencedRepositoryInterface
     */
    private function getRepository(string $entityClassName): ServiceProviderReferencedRepositoryInterface
    {
        $repository = $this->entityManager->getRepository($entityClassName);
        if (!($repository instanceof ServiceProviderReferencedRepositoryInterface)) {
            throw new EntityResolveMethodNotAllowedException(
                sprintf('Entity with name "%s" not allowed resolve by alias', $entityClassName)
            );
        }

        return $repository;
    }
}