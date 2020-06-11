<?php


namespace Apl\HotelsDbBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Class EmbeddableServiceProviderReference
 *
 * @package Apl\HotelsDbBundle\Entity
 * @ORM\Embeddable()
 */
class EmbeddableServiceProviderReference implements \JsonSerializable
{
    /**
     * @ORM\Embedded(class="Apl\HotelsDbBundle\Entity\NullableServiceProviderAlias", columnPrefix="service_provider_")
     * @var NullableServiceProviderAlias
     */
    private $alias;

    /**
     * @ORM\Column(name="service_provider_reference", type="string", nullable=true)
     * @var string
     */
    private $reference;

    /**
     * EmbeddableServiceProviderReference constructor.
     *
     * @param string|ServiceProviderAlias|null $alias
     * @param null|string $reference
     */
    public function __construct($alias = null, ?string $reference = null)
    {
        $this
            ->setAlias(
                $alias
                    ? (\is_string($alias) ? new ServiceProviderAlias($alias) : $alias)
                    : new NullableServiceProviderAlias(null)
            )
            ->setReference($reference);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->getAlias()}:{$this->getReference()}";
    }

    /**
     * @return NullableServiceProviderAlias
     */
    public function getAlias(): NullableServiceProviderAlias
    {
        return $this->alias;
    }

    /**
     * @param ServiceProviderAlias|NullableServiceProviderAlias $alias
     * @return EmbeddableServiceProviderReference
     */
    public function setAlias(ServiceProviderAlias $alias): EmbeddableServiceProviderReference
    {
        if (!$alias instanceof NullableServiceProviderAlias) {
            $alias = new NullableServiceProviderAlias($alias->getAlias());
        }

        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return EmbeddableServiceProviderReference
     */
    public function setReference(?string $reference): EmbeddableServiceProviderReference
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return ['alias' => (string)$this->getAlias(), 'reference' => $this->getReference()];
    }
}