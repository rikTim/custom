<?php


namespace Base\HotelsDbBundle\Consumers;


use Base\HotelsDbBundle\Entity\Money\PriceCheckpoint;
use Base\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Base\HotelsDbBundle\Service\LoggerAwareTrait;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class HotelCalculateCheckpointPriceConsumer implements ConsumerInterface
{

    use LoggerAwareTrait,
        EntityManagerAwareTrait;

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Base\HotelsDbBundle\Exception\RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(AMQPMessage $msg)
    {
        $this->logger->info('Run calculate checkpoint price');
        $data = json_decode($msg->getBody(), true);
        $repository = $this->entityManager->getRepository(PriceCheckpoint::class);
        $repository->insert($data);
    }
}