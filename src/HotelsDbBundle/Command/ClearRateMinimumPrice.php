<?php


namespace Apl\HotelsDbBundle\Command;


use Apl\HotelsDbBundle\Entity\Money\Price\MinimumPrice;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearRateMinimumPrice extends ContainerAwareCommand
{

    use LoggerAwareTrait,
        EntityManagerAwareTrait;

    private const OPTION_PERIOD_OF_EXISTENCE = 'period_of_existence';

    public function configure()
    {
        $this
            ->setName('apl:hotels:rate_minimum_price:clear')
            ->addOption(self::OPTION_PERIOD_OF_EXISTENCE, 'f', InputOption::VALUE_REQUIRED, 'Period of existence ','P1D');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('Run start clear rate minimum price command');

        $date = new \DateTime();
        $interval = new \DateInterval($input->getOption(self::OPTION_PERIOD_OF_EXISTENCE));
        $date->sub($interval);

        $repository = $this->entityManager->getRepository(MinimumPrice::class);
        $count = $repository->deleteToDateUpdate($date);
        $this->logger->info('Delete rows:' . $count);
    }
}