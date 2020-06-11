<?php


namespace Apl\HotelsDbBundle\Command;


use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\EntityVersion\EntityVersionManagerAwareTrait;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ArrayGetterMapped;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDataService;
use Apl\HotelsDbBundle\Service\ServiceProvider\ServiceProviderManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePublishStatusHotelsCommand extends ContainerAwareCommand
{



    use EntityManagerAwareTrait,
        EntityVersionManagerAwareTrait,
        LoggerAwareTrait;

    /**
     * @var ImportDataService
     */
    private $importDataService;

    /**
     * @param ImportDataService $importDataService
     * @required
     */
    public function setImportDataService(ImportDataService $importDataService):void
    {
        $this->importDataService = $importDataService;
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this
            ->setName('apl:hotels:update:published');
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('Run update hotels publish status');
        $hotels = $this->entityManager->getRepository(Hotel::class)->findHotelsToUpdatePublishStatus();
        if(\count($hotels)>0) {
            /**
             * @var  Hotel $hotel
             */
            foreach ($hotels as $hotel) {
                $entityVersion = $this->entityVersionManager->createVersion($hotel, new ArrayGetterMapped(['published' => false]), ServiceProviderManager::SYSTEM_SERVICE_PROVIDER);
                $this->entityManager->persist($entityVersion);
                $this->entityVersionManager->useVersionAsActive($entityVersion);
            }
            $this->entityManager->flush();
        }
        $this->logger->info('Update hotels publish status complete, total hotels: '.\count($hotels));

    }
}