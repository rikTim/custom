<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;

interface ServiceProviderReferenceInterface
{
    /**
     * Связанный объект создал текущий объект
     */
    const MAPPING_TYPE_PARENTAL = 0;

    /**
     * Связанный объект полностью совпал с текущим объектом
     */
    const MAPPING_TYPE_AUTO_FULL_MATCH = 1;

    /**
     * Связанный объект с определенной вероятностью является текущим объектом
     */
    const MAPPING_TYPE_AUTO_PROBABILISTIC = 2;

    /**
     * Связанный объект сопоставили с текущим в ручном режиме
     */
    const MAPPING_TYPE_MANUAL = 3;

    /**
     * @return ServiceProviderReferencedEntityInterface
     */
    public function getEntity(): ?ServiceProviderReferencedEntityInterface;

    /**
     * @param ServiceProviderReferencedEntityInterface $entity
     * @return ServiceProviderReferenceInterface
     */
    public function setEntity(ServiceProviderReferencedEntityInterface $entity): ServiceProviderReferenceInterface;

    /**
     * @return ServiceProviderAlias
     */
    public function getAlias(): ?ServiceProviderAlias;

    /**
     * @param ServiceProviderAlias $alias
     * @return $this
     */
    public function setAlias(ServiceProviderAlias $alias): ServiceProviderReferenceInterface;

    /**
     * @return string
     */
    public function getReference(): ?string;

    /**
     * @param string $reference
     * @return $this
     */
    public function setReference(string $reference): ServiceProviderReferenceInterface;

    /**
     * @return int
     */
    public function getType(): ?int;

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): ServiceProviderReferenceInterface;
}