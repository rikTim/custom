<?php


namespace Apl\HotelsDbBundle\Service\Money\Price\RoomPrice;


use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Hotel\HotelRoom;
use Apl\HotelsDbBundle\Entity\Money\Price\AbstractClientRoomPrice;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RoomPriceDTO
 *
 * @package Apl\HotelsDbBundle\Service\Money\Price\RoomPrice
 *
 * TODO: remove this class in WEB-300 issue
 */
class RoomPriceDTO extends AbstractClientRoomPrice implements \Serializable
{
    /**
     * @var int
     */
    private $hotelId;

    /**
     * @var int
     */
    private $roomId;

    /**
     * @var int
     */
    private $boardId;

    /**
     * @var string|null
     */
    private $rateCommentsId;

    /**
     * @return GetterMapping
     */
    public function getGetterMapping(): GetterMapping
    {
        return parent::getGetterMapping()->addAttribute(new GetterAttribute('rateCommentsId'));
    }

    /**
     * @return SetterMapping
     */
    public function getSetterMapping(): SetterMapping
    {
        return parent::getSetterMapping()->addAttribute(ScalarHydrator::getStringAttribute('rateCommentsId'));
    }

    /**
     * @return int
     */
    public function getHotelId(): int
    {
        if (!$this->hotelId) {
            if (!$this->getHotel()) {
                throw new RuntimeException(sprintf('Can`t find hotel id on %s', \get_class($this)));
            }

            $this->hotelId = $this->getHotel()->getId();
        }

        return $this->hotelId;
    }

    /**
     * @param int $hotelId
     * @return RoomPriceDTO
     */
    public function setHotelId(int $hotelId): RoomPriceDTO
    {
        $this->hotelId = $hotelId;
        return $this;
    }

    /**
     * @return int
     */
    public function getRoomId(): int
    {
        if (!$this->roomId) {
            if (!$this->getRoom()) {
                throw new RuntimeException(sprintf('Can`t find room id on %s', \get_class($this)));
            }

            $this->roomId = $this->getRoom()->getId();
        }

        return $this->roomId;
    }

    /**
     * @param int $roomId
     * @return RoomPriceDTO
     */
    public function setRoomId(int $roomId): RoomPriceDTO
    {
        $this->roomId = $roomId;
        return $this;
    }

    /**
     * @return int
     */
    public function getBoardId(): int
    {
        if (!$this->boardId) {
            if (!$this->getBoard()) {
                throw new RuntimeException(sprintf('Can`t find board id on %s', \get_class($this)));
            }

            $this->boardId = $this->getBoard()->getId();
        }

        return $this->boardId;
    }

    /**
     * @param int $boardId
     * @return RoomPriceDTO
     */
    public function setBoardId(int $boardId): RoomPriceDTO
    {
        $this->boardId = $boardId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRateCommentsId(): ?string
    {
        return $this->rateCommentsId;
    }

    /**
     * @param string|null $rateCommentsId
     * @return RoomPriceDTO
     */
    public function setRateCommentsId(?string $rateCommentsId): RoomPriceDTO
    {
        $this->rateCommentsId = $rateCommentsId;
        return $this;
    }

    /**
     * String representation of object
     *
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return \serialize([
            'hotelId' => $this->getHotelId(),
            'roomId' => $this->getRoomId(),
            'boardId' => $this->getBoardId(),
            'rateCommentsId' => $this->getRateCommentsId(),

            'serviceProviderReference' => $this->getServiceProviderReference(),
            'checkIn' => $this->getCheckIn(),
            'checkOut' => $this->getCheckOut(),
            'roomQuantity' => $this->getRoomQuantity(),
            'adults' => $this->getAdults(),
            'children' => $this->getChildren(),
            'childrenAges' => $this->getChildrenAges(),
            'rateNet' => $this->getRateNet(),
            'rateClass' => $this->getRateClass(),
            'rateType' => $this->getRateType(),
            'paymentType' => $this->getPaymentType(),
            'packaging' => $this->isPackaging(),
            'rawCancellationPolicies' => $this->getRawCancellationPolicies(),
            'rawTaxes' => $this->getRawTaxes(),
            'offers' => $this->getOffers(),
            'created' => $this->getCreated(),
        ]);
    }

    /**
     * Constructs the object
     *
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized, ['allowed_classes' => true]);

        $this
            ->setHotelId($unserialized['hotelId'])
            ->setRoomId($unserialized['roomId'])
            ->setBoardId($unserialized['boardId'])
            ->setRateCommentsId($unserialized['rateCommentsId'] ?? null)
            ->setServiceProviderReference($unserialized['serviceProviderReference'])
            ->setCheckIn($unserialized['checkIn'])
            ->setCheckOut($unserialized['checkOut'])
            ->setRoomQuantity($unserialized['roomQuantity'])
            ->setAdults($unserialized['adults'])
            ->setChildren($unserialized['children'])
            ->setChildrenAges($unserialized['childrenAges'])
            ->setRateNet($unserialized['rateNet'])
            ->setRateClass($unserialized['rateClass'])
            ->setRateType($unserialized['rateType'])
            ->setPaymentType($unserialized['paymentType'])
            ->setPackaging($unserialized['packaging'])
            ->setRawCancellationPolicies($unserialized['rawCancellationPolicies'])
            ->setRawTaxes($unserialized['rawTaxes'])
            ->setOffers($unserialized['offers'])
            ->setCreated($unserialized['created']);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @throws \Doctrine\ORM\ORMException
     */
    public function initialize(EntityManagerInterface $entityManager)
    {
        if ($this->hotelId) {
            $this->setHotel($entityManager->getReference(Hotel::class, $this->hotelId));
        }

        if ($this->roomId) {
            $this->setRoom($entityManager->getReference(HotelRoom::class, $this->roomId));
        }

        if ($this->boardId) {
            $this->setBoard($entityManager->getReference(Board::class, $this->boardId));
        }
    }
}