<?php


namespace Apl\HotelsDbBundle\Command;


use Apl\HotelsDbBundle\Repository\SearchIndex\AbstractSearchIndexRepository;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\Search\SearchIndexEntityInterface;
use Apl\HotelsDbBundle\Service\Search\SearchIndexRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateSearchIndexCommand
 *
 * @package Apl\HotelsDbBundle\Command
 */
class UpdateSearchIndexCommand extends ContainerAwareCommand
{
    use EntityManagerAwareTrait;

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this
            ->setName('apl:hotels:search:index:update')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SearchIndexRepositoryInterface[] $repositories */
        $repositories = [];
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            if (is_subclass_of($metadata->getName(), SearchIndexEntityInterface::class)) {
                $repository = $this->entityManager->getRepository($metadata->getName());
                if (is_subclass_of($repository, SearchIndexRepositoryInterface::class)) {
                    $repositories[] = $this->entityManager->getRepository($metadata->getName());
                }
            }
        }

        foreach ($repositories as $repository) {
            $repository->updateSearchIndex();
        }
    }
}