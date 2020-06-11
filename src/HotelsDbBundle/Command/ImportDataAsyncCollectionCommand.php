<?php


namespace Base\HotelsDbBundle\Command;


use Base\HotelsDbBundle\Exception\InvalidArgumentException;
use Base\HotelsDbBundle\Service\ConsoleService\AsyncMultiplyProgressBar;
use Base\HotelsDbBundle\Service\LoggerAwareTrait;
use Base\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDataService;
use Base\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\StaticDataCollectionInterface;
use Base\HotelsDbBundle\Service\ServiceProvider\ServiceProviderManagerAwareTrait;
use Doctrine\DBAL\ConnectionException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportDataAsyncCollectionCommand
 *
 * @package Base\HotelsDbBundle\Command
 */
class ImportDataAsyncCollectionCommand extends ContainerAwareCommand
{
    use LoggerAwareTrait,
        ServiceProviderManagerAwareTrait;

    public const NAME = 'apl:hotels:sub_process:import';

    private const ARG_SOURCE = 'source';

    /**
     * @var ImportDataService
     */
    private $importDataService;

    /**
     * @inheritdoc
     */
    public function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->addArgument(self::ARG_SOURCE, InputArgument::REQUIRED, 'Source provider for import static data for destinations');
    }

    /**
     * @param ImportDataService $importDataService
     * @required
     */
    public function setImportDataService(ImportDataService $importDataService): void
    {
        $this->importDataService = $importDataService;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws ConnectionException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stdin = fopen('php://stdin', 'r');

        ['collection' => $collection, 'progressBarKey' => $progressBarKey] = @unserialize(
            stream_get_contents($stdin),
            ['allow_classes' => true]
        );

        fclose($stdin);

        if (!\is_object($collection) || !$collection instanceof StaticDataCollectionInterface) {
            throw new InvalidArgumentException('Incorrect input');
        }

        $this->importDataService->withServiceProvider(
            $this->serviceProviderManager->getServiceProvider($input->getArgument(self::ARG_SOURCE))->getServiceProviderAlias(),
            $progressBarKey ? (new AsyncMultiplyProgressBar($progressBarKey))->withOutput($output) : null
        )->import($collection);
    }
}
