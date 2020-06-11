<?php


namespace Apl\HotelsDbBundle\Command;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Apl\HotelsDbBundle\Repository\TranslateType\TranslatableStringRepository;
use Apl\HotelsDbBundle\Repository\TranslateType\TranslatableTextRepository;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerAwareTrait;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Remedge\YandexTranslateBundle\Manager\TranslateManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class TranslateCommand extends ContainerAwareCommand
{

    use LoggerAwareTrait,
        EntityManagerAwareTrait;

    private const OPTION_FROM_LOCALE = 'from_locale';
    private const OPTION_TO_LOCALE = 'to_locale';

    /**
     * @var TranslateManager
     */
    private $translateManager;

    /**
     * @param TranslateManager $translateManager
     * @required
     */
    public function setTranslateManager(TranslateManager $translateManager): void
    {
        $this->translateManager = $translateManager;
    }

    public function configure()
    {
        $this
            ->setName('apl:hotels:translate:auto')
            ->addOption(self::OPTION_FROM_LOCALE, 'f', InputOption::VALUE_REQUIRED, 'Locale name from translate', 'en')
            ->addOption(self::OPTION_TO_LOCALE, 't', InputOption::VALUE_REQUIRED, 'Locale name to translate', 'ru');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Remedge\YandexTranslateBundle\Exception\FailedTranslationException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Apl\HotelsDbBundle\Exception\InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {

        $this->logger->info('Run start translate command');

        $classes = [
            TranslatableString::class,
            TranslatableText::class
        ];
        $fromLocale = new Locale( $input->getOption(self::OPTION_FROM_LOCALE));
        $toLocale = new Locale($input->getOption(self::OPTION_TO_LOCALE));

        if ($fromLocale === $toLocale) {
            throw new InvalidArgumentException('No translate from' . $fromLocale->getLanguageCode() . ' to ' . $toLocale->getLanguageCode());
        }
        $translateAlias = $fromLocale->getLanguageCode() . '-' . $toLocale->getLanguageCode();
        $length = 0;
        foreach ($classes as $class) {

            $repository = $this->entityManager->getRepository($class);
            $translatesArray = $repository->findNoTranslate($fromLocale, $toLocale);
            $data = [];
            $to_translates = [];

            if (\count($translatesArray)) {
                foreach ($translatesArray as $translate) {
                    $data[md5($translate['search'])][] = $translate;
                    $to_translates[md5($translate['search'])] = trim($translate['search']);
                }


                $success_translate = [];
                foreach ($to_translates as $key => $to_translate) {
                    $length += mb_strlen($to_translate);
                    if ($length >= 1000000) {
                        break 2;
                    }

                    $result = $this->translateManager->translate($to_translate, $translateAlias);
                    if (array_key_exists($key, $data)) {
                        foreach ($data[$key] as $value) {
                            $value['search'] = trim($result);
                            $value['locale'] = $toLocale->getLocale();
                            $value['entityAlias'] = addslashes($value['entityAlias']);
                            $success_translate[] = $value;
                        }
                    }
                }
                if (\count($success_translate)) {
                    $count = $repository->insertNewTranslates($success_translate);
                    $this->logger->info('Success translate ' . $count);
                }
            }
        }
    }
}