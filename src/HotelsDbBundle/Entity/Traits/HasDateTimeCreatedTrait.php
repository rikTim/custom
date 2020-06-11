<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait HasDateTimeCreatedTrait
 * @package Apl\HotelsDbBundle\Entity\Traits
 */
trait HasDateTimeCreatedTrait
{
    /**
     * @ORM\Column(name="created", type="datetime_immutable")
     * @Groups({"secured"})
     * @var \DateTimeImmutable
     */
    private $created;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @param \DateTimeImmutable $created
     * @return $this
     */
    public function setCreated(\DateTimeImmutable $created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function onPrePersistSetCreated(): void
    {
        if (null === $this->created) {
            $this->created = new \DateTimeImmutable();
        }
    }
}