<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData;


use Apl\HotelsDbBundle\Command\ImportDataAsyncCollectionCommand;
use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\Exception\LogicException;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ConsoleService\AsyncMultiplyProgressBar;
use Apl\HotelsDbBundle\Service\ConsoleService\MultiplyProgressBarInterface;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionManagerAwareTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\VersionedEntityInterface;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ObjectDataManipulatorAwareTrait;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateManagerAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ReferencedEntityResolverAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Apl\RabbitBundle\Service\ProducerManagerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\TransactionIsolationLevel;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class ImportDataService
 *
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData
 */
class ImportDataService
{
    use EntityManagerAwareTrait,
        ReferencedEntityResolverAwareTrait,
        TranslateManagerAwareTrait,
        EntityVersionManagerAwareTrait,
        ObjectDataManipulatorAwareTrait,
        ProducerManagerAwareTrait,
        LoggerAwareTrait;

    /**
     * @var ServiceProviderAlias
     */
    private $serviceProviderAlias;

    /**
     * @var array
     */
    private $statistic = [];

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var MultiplyProgressBarInterface
     */
    private $multiplyProgressBar;

    /**
     * @var Process[]
     */
    private $subProcesses = [];

    /**
     * @var int
     */
    private $maxSubProcesses = 8;

    /**
     * ImportDataService constructor.
     *
     * @param Stopwatch $stopwatch
     */
    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * @param ServiceProviderAlias $serviceProviderAlias
     * @param MultiplyProgressBarInterface|null $multiplyProgressBar
     * @param int $maxSubProcess
     * @return ImportDataService
     * @throws \Doctrine\DBAL\DBALException
     */
    public function withServiceProvider(
        ServiceProviderAlias $serviceProviderAlias,
        ?MultiplyProgressBarInterface $multiplyProgressBar = null,
        int $maxSubProcess = 8
    ): ImportDataService
    {
        $importService = clone $this;
        $importService->serviceProviderAlias = $serviceProviderAlias;
        $importService->statistic = [];

        $importService->multiplyProgressBar = $multiplyProgressBar;
        $importService->maxSubProcesses = $maxSubProcess;

        $connection = $this->entityManager->getConnection();

        $connection->setAutoCommit(true);
        $connection->setTransactionIsolation(TransactionIsolationLevel::REPEATABLE_READ);
        $connection->query('SET SESSION wait_timeout = 2000')->execute();

        return $importService;
    }

    /**
     * @return array
     */
    public function getStatistic(): array
    {
        return $this->statistic;
    }

    /**
     * @return int
     */
    public function getMaxSubProcesses(): int
    {
        return $this->maxSubProcesses;
    }

    /**
     * @param StaticDataCollectionInterface $collection
     */
    public function importAsync(StaticDataCollectionInterface $collection): void
    {
        if ($this->maxSubProcesses <= 0) {
            $this->import($collection);
            return;
        }

        $progressBarKey = $this->multiplyProgressBar ? spl_object_hash($this->multiplyProgressBar) : null;

        $process = new Process((new PhpExecutableFinder())->find() . ' bin/console ' . ImportDataAsyncCollectionCommand::NAME . ' ' . (string)$this->serviceProviderAlias);
        $process->enableOutput();
        $process->setInput(serialize([
            'collection' => $collection,
            'progressBarKey' => $progressBarKey,
        ]));

        $this->asyncWait($this->maxSubProcesses);
        $this->subProcesses[] = $process;

        $process->start(function ($type, $buffer) use ($progressBarKey) {
            if ($progressBarKey && $type === Process::OUT) {
                foreach (explode(PHP_EOL, $buffer) as $line) {
                    if (strpos($line, $progressBarKey) === 0) {
                        AsyncMultiplyProgressBar::processCommand($this->multiplyProgressBar, $line);
                    }
                }
            }

            if ($type === Process::ERR) {
                $this->logger->error('Async process error: ' . $buffer);
            } else {
                $this->logger->debug('Async process ' . $type  . ': '. $buffer);
            }
        });

    }

    /**
     * @param int $maxRunningProcesses
     * @param int $sleep
     */
    public function asyncWait(int $maxRunningProcesses = 0, int $sleep = 2)
    {
        do {
            $this->asyncTick();

            if (\count($this->subProcesses) > $maxRunningProcesses) {
                sleep($sleep);
            } else {
                return;
            }
        } while (true);
    }

    public function asyncTick()
    {
        foreach ($this->subProcesses as $index => $subProcess) {
            if (!$subProcess->isRunning()) {
                $this->logger->info('Sub process end with code: ' . $subProcess->getExitCode());
                unset($this->subProcesses[$index]);
            }
        }
    }

    /**
     * @param StaticDataCollectionInterface $collection
     * @throws \Apl\HotelsDbBundle\Exception\LogicException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function import(StaticDataCollectionInterface $collection): void
    {
        $baseEntityClassName = $collection->getEntityClassName();
        $collectionSize = \count($collection);
        $this->logger->info('Call import with collection', ['entityClassName' => $baseEntityClassName, 'collectionSize' => $collectionSize]);

        if (!$this->serviceProviderAlias) {
            throw new LogicException('Not allowed import without service provider');
        }

        $connection = $this->entityManager->getConnection();

        $i = 0;
        do {
            try {
                $connection->beginTransaction();
                break;
            } catch (ConnectionException $e) {
                sleep(1);
            }
        } while($i++ <= 10);

        if ($i >= 10 && !$connection->isTransactionActive()) {
            throw $e;
        }

        try {
            $this->entityManager->clear();
            $this->referencedEntityResolver->clearSelfReferences();

            $this->persistCollectionRecursive($collection);

            $this->entityManager->flush();
            $this->translateManager->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param StaticDataCollectionInterface $collection
     * @return Collection
     * @throws \Apl\HotelsDbBundle\Exception\LogicException
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     */
    private function persistCollectionRecursive(StaticDataCollectionInterface $collection, ?int &$childVersionsCounter = null, ?int &$childNewEntities = null): Collection
    {
        $isChildren = $childVersionsCounter !== null;
        if (!$isChildren) {
            $childVersionsCounter = 0;
            $childNewEntities = 0;
        }

        $entityClassName = $collection->getEntityClassName();

        if (!$isChildren && $this->multiplyProgressBar) {
            $progressBar = $this->multiplyProgressBar->newTask(
                \count($collection),
                '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %memory:6s% | Entity: ' . $entityClassName
            );
        }

        $timerResolve = $this->stopwatch->start('resolve_collection_tick');

        /** @var \SplObjectStorage|ServiceProviderReferencedEntityInterface[] $mappedCollection */
        $mappedCollection = $this->referencedEntityResolver->resolve(
            $entityClassName,
            $this->serviceProviderAlias,
            iterator_to_array($collection),
            $collection->getParentEntity()
        );

        $resolveDuration = $timerResolve->stop()->getDuration();
        $this->statistic[$entityClassName]['resolve'] = ($this->statistic[$entityClassName]['resolve'] ?? 0) + $resolveDuration;
        $this->logger->info('Collection resolved', ['entityClassName' => $entityClassName, 'duration' => $resolveDuration, 'memory' => $timerResolve->getMemory()]);

        if ($entityClassName instanceof TranslatableObjectInterface) {
            // Загружаем все локали, потому что для версии создаются для всех локалей
            $this->translateManager->loadTranslationsForAllObjects($mappedCollection);
        }

        $timerPersist = $this->stopwatch->start('persist_collection_' . $entityClassName);
        $persistedCollection = new ArrayCollection();
        $newVersionsCounter = 0;
        $newEntities = 0;
        foreach ($mappedCollection as $entity) {
            $isNewEntity = $this->referencedEntityResolver->isNewEntity($entity);
            $newEntities += (int)$isNewEntity;

            $importDTO = $mappedCollection[$entity];

            if (!($importDTO instanceof ImportDTO)) {
                throw new RuntimeException('Incorrect import data format');
            }

            // Рекурсивно создаем все дочерние сущности
            $timerPersist->stop();
            foreach ($importDTO->getFields() as $key => $data) {
                if ($data instanceof StaticDataCollectionInterface && \count($data)) {
                    $data->setParentEntity($entity);
                    $importDTO->resolveCollection($key, $this->persistCollectionRecursive($data, $childVersionsCounter, $childNewEntities));
                }
            }
            $timerPersist->start();

            // Persist or update resolved entity
            if ($entity instanceof VersionedEntityInterface) {
                $entityVersion = $this->entityVersionManager->createVersion($entity, $importDTO, $this->serviceProviderAlias);

                // Обновляем неверсионные данны (например связи между сущностями)
                $setterMapping = $this->objectDataManipulator->diffSetterMapping($entity, $entityVersion);
                if (\count($setterMapping)) {
                    $this->objectDataManipulator->hydrate($entity, $importDTO, $setterMapping);
                }

                // Checking on duplicate latest version from current service provider
                if (!$this->entityVersionManager->isHasSameVersion($entityVersion)) {
                    $this->entityManager->persist($entityVersion);

                    if ($entityVersion instanceof TranslatableObjectInterface) {
                        $this->translateManager->persistTranslations($entityVersion);
                    }

                    $newVersionsCounter++;
                    $this->statistic[$entityClassName]['versions'] = ($this->statistic[$entityClassName]['versions'] ?? 0) + 1;

                    if (
                        // On create entity use crated version as active default
                        (!$entity->getActiveVersion() && $isNewEntity)
                        // On update data from current active service provider - set new version as active
                        || (
                            ($currentResponsible = $entity->getActiveVersion()->getResponsibleAlias())
                            && $currentResponsible->isEqual($entityVersion->getResponsibleAlias())
                        )
                    ) {
                        $this->entityVersionManager->useVersionAsActive($entityVersion);
                    }
                }
            } elseif ($entity instanceof HasSetterMappingInterface) {
                $this->objectDataManipulator->hydrate($entity, $importDTO);
            } else {
                throw new LogicException(sprintf('Cannot update entity data for entity "%s"', \get_class($entity)));
            }

            $this->entityManager->persist($entity);

            if ($entity instanceof TranslatableObjectInterface) {
                $this->translateManager->persistTranslations($entity);
            }

            $persistedCollection->add($entity);
            if (isset($progressBar)) {
                $this->multiplyProgressBar->tick($progressBar);
            }
        }

        $persistDuration = $timerPersist->stop()->getDuration();
        $this->logger->info('Collection persisted', ['entityClassName' => $entityClassName, 'duration' => $persistDuration, 'memory' => $timerPersist->getMemory()]);
        $this->statistic[$entityClassName]['persist'] = ($this->statistic[$entityClassName]['persist'] ?? 0) + $persistDuration;

        if (isset($progressBar)) {
            $this->multiplyProgressBar->endProgress(
                $progressBar,
                '[%elapsed:6s% %memory:6s% versions: ' . $newVersionsCounter . ($childVersionsCounter > 0 ? ' (' . $childVersionsCounter . ')' : '')
                    . ' entities: ' . $newEntities . ($newEntities > 0 ? ' (' . $newEntities . ')' : '')
                    . '] Import ' . $entityClassName . ' completed'
            );
        }

        if ($isChildren) {
            $childVersionsCounter += $newVersionsCounter;
            $childNewEntities += $newEntities;
        }

        return $persistedCollection;
    }
}