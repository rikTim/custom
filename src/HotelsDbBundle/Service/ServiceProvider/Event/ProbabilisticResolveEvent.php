<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\Event;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDTO;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;
use Symfony\Component\EventDispatcher\Event;

class ProbabilisticResolveEvent extends Event
{
    const NAME = 'entity_resolver.probabilistic_resolve';

    /**
     * @var ServiceProviderAlias
     */
    private $serviceProviderAlias;

    /**
     * @var string
     */
    private $entityClassName;

    /**
     * @var array
     */
    private $mappingData;

    /**
     * @var ServiceProviderReferencedEntityInterface[]
     */
    private $resolved = [];

    /**
     * ProbabilisticResolveEvent constructor.
     * @param ServiceProviderAlias $serviceProviderAlias
     * @param string $entityClassName
     * @param array $mappingData
     */
    public function __construct(ServiceProviderAlias $serviceProviderAlias, string $entityClassName, array $mappingData)
    {
        $this->serviceProviderAlias = $serviceProviderAlias;
        $this->entityClassName = $entityClassName;
        $this->mappingData = $mappingData;
    }

    /**
     * @return ServiceProviderAlias
     */
    public function getServiceProviderAlias(): ServiceProviderAlias
    {
        return $this->serviceProviderAlias;
    }

    /**
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * @return ImportDTO[]|array
     */
    public function getMappingData(): array
    {
        return $this->mappingData;
    }

    /**
     * @param string $dataReference
     * @param ServiceProviderReferencedEntityInterface $entity
     */
    public function resolve(string $dataReference, ServiceProviderReferencedEntityInterface $entity): void
    {
        if (isset($this->resolved[$dataReference]) && $this->resolved[$dataReference] !== $entity) {
            throw new RuntimeException(sprintf('Entity with reference "%s" already resolved', $dataReference));
        }

        if (!isset($this->mappingData[$dataReference])) {
            throw new RuntimeException(sprintf('Undefined reference "%s" in data list', $dataReference));
        }

        if (!is_object($entity) || !($entity instanceof $this->entityClassName)) {
            throw new RuntimeException('Incorrect resolved entity type');
        }

        $this->resolved[$dataReference] = $entity;
        unset($this->mappingData[$dataReference]);

        if (!$this->mappingData) {
            $this->stopPropagation();
        }
    }

    /**
     * @return ServiceProviderReferencedEntityInterface[]
     */
    public function getResolved(): array
    {
        return $this->resolved;
    }
}