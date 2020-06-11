<?php


namespace Apl\HotelsDbBundle\Entity\Traits;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait HasIntegerIdTrait
 *
 * @package Apl\HotelsDbBundle\Entity\Traits
 */
trait HasIntegerIdTrait
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue
     * @var integer|null
     * @Groups({"id", "secured"})
     */
    private $id;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id)
    {
        $this->id = $id;
        return $this;
    }
}