<?php


namespace Base\HotelsDbBundle\Command;


use Base\HotelsDbBundle\Entity\Locale;
use Base\HotelsDbBundle\Exception\InvalidArgumentException;
use Base\HotelsDbBundle\Service\LocaleDetector\LocaleDetectorAwareTrait;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Apl\HotelsDbBundle\Service\ConsoleService\MultiplyProgressBar;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDataService;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ReferencedEntityResolverAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\ServiceProviderManagerAwareTrait;
use Apl\HotelsDbBundle\Service\ServiceProvider\Criteria\StaticDataCriteria;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportStaticDataCommand
 * @package Apl\HotelsDbBundle\Command
 */
class ImportStaticDataCommand extends ContainerAwareCommand
{
    use ServiceProviderManagerAwareTrait,
        ReferencedEntityResolverAwareTrait,
        LocaleDetectorAwareTrait,
        LoggerAwareTrait;

    private const ARG_SOURCE = 'source';

    private const OPTION_CURRENT_LOCALE = 'locale';
    private const OPTION_UPDATE_DATE_TIME = 'updated';
    private const OPTION_BULK_SIZE_LIMIT = 'bulk';
    private const OPTION_FIRST_PAGE = 'from';
    private const OPTION_STEPS = 'step';
    private const OPTION_FIRST_STEP = 'first_step';
    private const OPTION_ONLY_NEW = 'only_new';
    private const OPTION_MAX_SUB_PROCESS = 'max_sub_process';

    /**
     * @var ImportDataService
     */
    private $importDataService;

    /**
     * @var MultiplyProgressBar
     */
    private $multiplyProgressBar;

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this
            ->setName('apl:hotels:import')
            ->addArgument(self::ARG_SOURCE, InputArgument::REQUIRED, 'Source provider for import static data for destinations')
            ->addOption(self::OPTION_CURRENT_LOCALE, 'l', InputOption::VALUE_OPTIONAL, 'Locale name for import data', null)
            ->addOption(self::OPTION_UPDATE_DATE_TIME, 'u', InputOption::VALUE_OPTIONAL, 'Import only modified data after updated date time', null)
            ->addOption(self::OPTION_BULK_SIZE_LIMIT, 'b', InputOption::VALUE_OPTIONAL, 'Bulk size limit', 1000)
            ->addOption(self::OPTION_FIRST_PAGE, 'f', InputOption::VALUE_OPTIONAL, 'Start import from (using in import hotels)', 0)
            ->addOption(self::OPTION_STEPS, 's', InputOption::VALUE_OPTIONAL, 'Run only selected step(-s)', null)
            ->addOption(self::OPTION_FIRST_STEP, null, InputOption::VALUE_OPTIONAL, 'Run from selected step', null)
            ->addOption(self::OPTION_ONLY_NEW, null, InputOption::VALUE_NONE, 'Run from selected step')
            ->addOption(self::OPTION_MAX_SUB_PROCESS, 'm', InputOption::VALUE_OPTIONAL, 'Sub process count. Set 0 - for force sync import', 8)
        ;
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
     * @param MultiplyProgressBar $multiplyProgressBar
     * @required
     */
    public function setMultiplyProgressbar(MultiplyProgressBar $multiplyProgressBar): void
    {
        $this->multiplyProgressBar = $multiplyProgressBar;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('Run import static data command');

        $locale = ($currentLocale = $input->getOption(self::OPTION_CURRENT_LOCALE))
            ? new Locale($currentLocale)
            : $this->localeDetector->getCurrentLocale();

        $criteria = (new StaticDataCriteria())
            ->withLocale($locale)
            ->withBulkSizeLimit($input->getOption(self::OPTION_BULK_SIZE_LIMIT))
            ->withFirstPage($input->getOption(self::OPTION_FIRST_PAGE))
            ->withUpdatedDateTime($input->getOption(self::OPTION_UPDATE_DATE_TIME)
                ? new \DateTimeImmutable($input->getOption(self::OPTION_UPDATE_DATE_TIME))
                : null
            );

        if ($input->getOption(self::OPTION_ONLY_NEW)) {
            $this->referencedEntityResolver->setResolveOnlyNewEntity(true);
        }

        $serviceProviderNames = \explode(',', \strtolower(\trim($input->getArgument(self::ARG_SOURCE))));
        $steps = explode(',', $input->getOption(self::OPTION_STEPS));
        if ($steps !== null && \count($serviceProviderNames) > 1) {
            throw new InvalidArgumentException('Cant use certain step when given more than one service provider');
        }

        foreach ($serviceProviderNames as $serviceProviderName) {
            try {
                $serviceProvider = $this->serviceProviderManager->getServiceProvider($serviceProviderName);
            } catch (InvalidArgumentException $exception) {
                $this->logger->emergency('Incorrect service provider name', ['serviceProvider' => $serviceProviderName]);
                continue;
            }

            $this->logger->info('Starting import static data', ['serviceProvider' => $serviceProviderName]);

            $this->multiplyProgressBar = $this->multiplyProgressBar->withOutput($output);

            $activeImportDataService = $this->importDataService->withServiceProvider(
                $serviceProvider->getServiceProviderAlias(),
                $this->multiplyProgressBar,
                (int)$input->getOption(self::OPTION_MAX_SUB_PROCESS)
            );

            $scenario = $serviceProvider->getImportStaticDataScenario();
            $scenario->setMultiplyProgressBar($this->multiplyProgressBar)->setLogger($this->logger);

            if ($input->getOption(self::OPTION_FIRST_STEP)) {
                $scenario->runFromStep(
                    $input->getOption(self::OPTION_FIRST_STEP),
                    $criteria,
                    $activeImportDataService,
                    $this->multiplyProgressBar
                );
            } else {
                $scenario->runSteps(
                    $input->getOption(self::OPTION_STEPS),
                    $criteria,
                    $activeImportDataService,
                    $this->multiplyProgressBar
                );
            }

            $this->logger->info('Finished import static data', [
                'serviceProvider' => $serviceProviderName,
                'statistic' => $activeImportDataService->getStatistic()
            ]);
        }

        $this->logger->info('Finish import static data command');
    }
}