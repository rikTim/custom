<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait HasDateTimeUpdatedTrait
 * @package Apl\HotelsDbBundle\Entity\Traits
 */
trait HasDateTimeUpdatedTrait
{
    /**
     * @ORM\Column(name="updated", type="datetime_immutable")
     * @var \DateTimeImmutable
     * @Groups({"secured"})
     */
    private $updated;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdated(): ?\DateTimeImmutable
    {
        return $this->updated;
    }

    /**
     * @param \DateTimeImmutable $updated
     * @return $this
     */
    public function setUpdated(\DateTimeImmutable $updated): self
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * @ORM\PrePersist()
     *
     * @throws \Exception
     */
    public function onPrePersistSetUpdated(): void
    {
        if (!$this->getUpdated()) {
            $this->setUpdated(new \DateTimeImmutable());
        }
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param PreUpdateEventArgs $eventArgs
     * @throws \Exception
     */
    public function onPreUpdateSetUpdated(PreUpdateEventArgs $eventArgs): void
    {
        if (!$eventArgs->hasChangedField('updated')) {
            $this->setUpdated(new \DateTimeImmutable());
        }
    }
}