<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData;


use Apl\HotelsDbBundle\Exception\LogicException;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Class ImportDTO
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData
 */
class ImportDTO implements HasGetterMappingInterface, \ArrayAccess
{
    /**
     * @var string
     */
    private $externalReference;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var TranslateTypeInterface[]
     */
    private $translatableFields;

    /**
     * ImportDTO constructor.
     * @param string $externalReference
     * @param array $fields
     * @param TranslateTypeInterface[]|null[] $translatableFields
     */
    public function __construct(string $externalReference, array $fields, ?TranslateTypeInterface ...$translatableFields)
    {
        $this->externalReference = \trim($externalReference);
        array_walk_recursive($fields, function(&$item){
            if (\is_string($item)){
                $item = \trim($item);
            }
        });
        $this->fields = $fields;
        $this->translatableFields = array_filter($translatableFields);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getExternalReference();
    }

    /**
     * @return string
     */
    public function getExternalReference(): string
    {
        return $this->externalReference;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return TranslateTypeInterface[]
     */
    public function getTranslatableFields(): array
    {
        return $this->translatableFields;
    }

    /**
     * @param string $fieldName
     * @param string|int $reference
     * @param $resolved
     * @throws \Apl\HotelsDbBundle\Exception\LogicException
     */
    public function resolveReference(string $fieldName, $reference, $resolved): void
    {
        if (!\is_string($reference) && !\is_int($reference)) {
            throw new LogicException(sprintf('Incorrect reference type "%s" for field "%s"', \gettype($reference), $fieldName));
        }

        if (\is_scalar($this->fields[$fieldName]) && $this->fields[$fieldName] === $reference) {
            $this->fields[$fieldName] = $resolved;
        } elseif (\is_array($this->fields[$fieldName]) && ($key = \array_search($reference, $this->fields[$fieldName], true)) !== false) {
            $this->fields[$fieldName][$key] = $resolved;
        } else {
            throw new LogicException(sprintf('Undefined resolved reference "%s" for field "%s"', $reference, $fieldName));
        }
    }

    /**
     * @param string $fieldName
     * @param Collection $collection
     * @throws \Apl\HotelsDbBundle\Exception\LogicException
     */
    public function resolveCollection(string $fieldName, Collection $collection): void
    {
        if (isset($this->fields[$fieldName]) && $this->fields[$fieldName] instanceof StaticDataCollectionInterface) {
            $this->fields[$fieldName] = $collection;
        } else {
            throw new LogicException(sprintf('Undefined resolved collection for field "%s"', $fieldName));
        }
    }

    /**
     * @return GetterMapping
     */
    public function getGetterMapping(): GetterMapping
    {
        $mapping = new GetterMapping();

        foreach (array_keys($this->fields) as $key) {
            $mapping->addAttribute(new GetterAttribute($key));
        }

        if ($this->translatableFields) {
            $mapping->addAttribute(new GetterAttribute('translatesCollection', 'getTranslatableFields'));
        }

        return $mapping;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->fields);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->fields[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException(sprintf('Cannot change immutable object "%s"', get_class($this)));
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException(sprintf('Cannot change immutable object "%s"', get_class($this)));
    }
}