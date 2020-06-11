<?php


namespace Apl\HotelsDbBundle\Consumer;


use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportCollectionMessage;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDataService;
use Apl\RabbitBundle\Service\AMQP\AbstractConsumer;
use Apl\RabbitBundle\Service\AMQP\MessageInterface;

/**
 * Class ImportCollectionConsumer
 *
 * @package Apl\HotelsDbBundle\Consumer
 */
class ImportCollectionConsumer extends AbstractConsumer
{
    /**
     * @var ImportDataService
     */
    private $importDataService;

    /**
     * ImportCollectionConsumer constructor.
     *
     * @param ImportDataService $importDataService
     */
    public function __construct(ImportDataService $importDataService)
    {
        $this->importDataService = $importDataService;
    }

    /**
     * @return string
     */
    protected function getMessageClassName(): string
    {
        return ImportCollectionMessage::class;
    }

    /**
     * @param ImportCollectionMessage|MessageInterface $message
     * @return int
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function run(MessageInterface $message): int
    {
        $this->importDataService
            ->withServiceProvider($message->getServiceProviderAlias())
            ->import($message->getCollection());

        return self::MSG_ACK;
    }
}