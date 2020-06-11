<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData;


use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\RabbitBundle\Service\AMQP\SerializableMessage;

/**
 * Class ImportCollectionMessage
 *
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData
 */
class ImportCollectionMessage extends SerializableMessage
{
    public const ROUTING_KEY = 'import_collection';

    /**
     * ImportCollectionMessage constructor.
     *
     * @param \Apl\HotelsDbBundle\Entity\ServiceProviderAlias $serviceProviderAlias
     * @param \Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\StaticDataCollectionInterface $collection
     */
    public function __construct(ServiceProviderAlias $serviceProviderAlias, StaticDataCollectionInterface $collection)
    {
        parent::__construct(
            self::ROUTING_KEY,
            ['sp_alias' => $serviceProviderAlias, 'collection' => $collection]
        );
    }

    /**
     * @return \Apl\HotelsDbBundle\Entity\ServiceProviderAlias
     */
    public function getServiceProviderAlias(): ServiceProviderAlias
    {
        return $this->body['sp_alias'];
    }

    /**
     * @return \Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\StaticDataCollectionInterface
     */
    public function getCollection(): StaticDataCollectionInterface
    {
        return $this->body['collection'];
    }
}