<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData;


use Apl\HotelsDbBundle\Exception\DuplicateException;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferencedEntityInterface;

/**
 * Class StaticDataCollection
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData
 */
class StaticDataCollection implements StaticDataCollectionInterface
{
    /**
     * @var string
     */
    private $entityClassName;

    /**
     * @var ImportDTO[]
     */
    private $data = [];

    /**
     * @var ServiceProviderReferencedEntityInterface|null
     */
    private $parentEntity;

    /**
     * AbstractStaticDataCollection constructor.
     * @param string $entityClassName
     */
    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * @return ServiceProviderReferencedEntityInterface|null
     */
    public function getParentEntity(): ?ServiceProviderReferencedEntityInterface
    {
        return $this->parentEntity;
    }

    /**
     * @param ServiceProviderReferencedEntityInterface $parentEntity
     */
    public function setParentEntity(ServiceProviderReferencedEntityInterface $parentEntity): void
    {
        $this->parentEntity = $parentEntity;
    }

    /**
     * @param ImportDTO $object
     * @return StaticDataCollection
     * @throws \Apl\HotelsDbBundle\Exception\DuplicateException
     */
    public function attach(ImportDTO $object): StaticDataCollection
    {
        if (isset($this->data[$object->getExternalReference()])) {
            throw new DuplicateException(
                sprintf('Cannot attach static data twice with single reference "%s"', $object->getExternalReference()),
                $this->data[$object->getExternalReference()]
            );
        }

        $this->data[$object->getExternalReference()] = $object;

        return $this;
    }

    /**
     * Return the current element
     *
     * @return ImportDTO Can return any type.
     */
    public function current(): ImportDTO
    {
        return current($this->data);
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * Return the key of the current element
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key(): string
    {
        return $this->current()->getExternalReference();
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     */
    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return count($this->data);
    }
}