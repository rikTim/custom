<?php


namespace Apl\HotelsDbBundle\Entity\Money;

use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeCreatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasDateTimeUpdatedTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasIntegerIdTrait;
use Apl\HotelsDbBundle\Entity\Traits\HasMoneyTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PriceCheckpoint
 * @package Apl\HotelsDbBundle\Entity\Money
 * @ORM\Entity(repositoryClass="Apl\HotelsDbBundle\Repository\Money\PriceCheckpointRepository")
 * @ORM\Table(name="hotels_db_rate_price_checkpoint", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="PRICE_HOTEL_UNIQUE", columns={"hotel_id","board_id"})},
 *     indexes={
 *      @ORM\Index(name="HOTEL_INDEX", columns={"hotel_id"}),
 *      @ORM\Index(name="BOARD_INDEX", columns={"board_id"}),
 *      @ORM\Index(name="UPDATED_INDEX", columns={"updated"})
 *     }
 *   )
 * @ORM\HasLifecycleCallbacks()
 */
class PriceCheckpoint
{
    use HasIntegerIdTrait,
        HasMoneyTrait,
        HasDateTimeCreatedTrait,
        HasDateTimeUpdatedTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Hotel\Hotel")
     * @ORM\JoinColumn(name="hotel_id", referencedColumnName="id", nullable=false)
     * @var Hotel
     */
    private $hotel;

    /**
     * @ORM\ManyToOne(targetEntity="Apl\HotelsDbBundle\Entity\Dictionary\Board")
     * @ORM\JoinColumn(name="board_id", referencedColumnName="id", nullable=false)
     * @var Board
     */
    private $board;

    /**
     * @return Hotel
     */
    public function getHotel(): Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return PriceCheckpoint
     */
    public function setHotel(Hotel $hotel): PriceCheckpoint
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return Board
     */
    public function getBoard(): Board
    {
        return $this->board;
    }

    /**
     * @param Board $board
     * @return PriceCheckpoint
     */
    public function setBoard(Board $board): PriceCheckpoint
    {
        $this->board = $board;
        return $this;
    }
}