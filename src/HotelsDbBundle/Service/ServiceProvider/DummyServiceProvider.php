<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider;


use Apl\HotelsDbBundle\Entity\Dictionary\Accommodation;
use Apl\HotelsDbBundle\Entity\Dictionary\Board;
use Apl\HotelsDbBundle\Entity\Dictionary\Category;
use Apl\HotelsDbBundle\Entity\Dictionary\Chain;
use Apl\HotelsDbBundle\Entity\Dictionary\Currency;
use Apl\HotelsDbBundle\Entity\Dictionary\Facility;
use Apl\HotelsDbBundle\Entity\Dictionary\FacilityGroup;
use Apl\HotelsDbBundle\Entity\Dictionary\FacilityTypology;
use Apl\HotelsDbBundle\Entity\Dictionary\ImageType;
use Apl\HotelsDbBundle\Entity\Dictionary\Issue;
use Apl\HotelsDbBundle\Entity\Dictionary\Promotion;
use Apl\HotelsDbBundle\Entity\Dictionary\Room;
use Apl\HotelsDbBundle\Entity\Dictionary\Segment;
use Apl\HotelsDbBundle\Entity\Dictionary\Terminal;
use Apl\HotelsDbBundle\Entity\Geo\Coordinates;
use Apl\HotelsDbBundle\Entity\Hotel\Hotel;
use Apl\HotelsDbBundle\Entity\Hotel\HotelFacility;
use Apl\HotelsDbBundle\Entity\Hotel\HotelImage;
use Apl\HotelsDbBundle\Entity\Hotel\HotelInterestPoint;
use Apl\HotelsDbBundle\Entity\Hotel\HotelPhone;
use Apl\HotelsDbBundle\Entity\Hotel\HotelRoom;
use Apl\HotelsDbBundle\Entity\Hotel\HotelTerminal;
use Apl\HotelsDbBundle\Entity\Hotel\HotelRoomFacility;
use Apl\HotelsDbBundle\Entity\Hotel\HotelRoomStay;
use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Location\Country;
use Apl\HotelsDbBundle\Entity\Location\Destination;
use Apl\HotelsDbBundle\Entity\ServiceProviderAlias;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableText;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\HotelFilter\Collection\FilterCollectionInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDataService;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportDTO;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\ImportScenario;
use Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData\StaticDataCollection;
use Apl\HotelsDbBundle\Service\ServiceProvider\Criteria\StaticDataCriteriaInterface;
use Apl\HotelsDbBundle\Service\ServiceProvider\ReferencedEntityResolver\ServiceProviderReferenceInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DummyServiceProvider
 * @package Apl\HotelsDbBundle\Service\ServiceProvider
 */
class DummyServiceProvider implements ServiceProviderInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @var ServiceProviderAlias
     */
    private $alias;

    /**
     * @param ContainerInterface|null $container
     * @required
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function getServiceProviderAlias(): ServiceProviderAlias
    {
        if (!$this->alias) {
            $this->alias = new ServiceProviderAlias('dummy');
        }

        return $this->alias;
    }
    /**
     * @return ImportScenario
     * @throws \Apl\HotelsDbBundle\Exception\LogicException
     */
    public function getImportStaticDataScenario(): ImportScenario
    {
        $this->checkEnvironment();

        return (new ImportScenario())
            ->pushStep('countries', [$this, 'importCountries'])
            ->pushStep('destinations', [$this, 'importDestinations'])
            ->pushStep('accommodations', [$this, 'importAccommodations'])
            ->pushStep('boards', [$this, 'importBoards'])
            ->pushStep('categories', [$this, 'importCategories'])
            ->pushStep('segments', [$this, 'importSegments'])
            ->pushStep('chains', [$this, 'importChains'])
            ->pushStep('currencies', [$this, 'importCurrencies'])
            ->pushStep('facilityGroups', [$this, 'importFacilityGroups'])
            ->pushStep('facilityTypologies', [$this, 'importFacilityTypologies'])
            ->pushStep('facilities', [$this, 'importFacilities'])
            ->pushStep('issues', [$this, 'importIssues'])
            ->pushStep('promotions', [$this, 'importPromotions'])
            ->pushStep('rooms', [$this, 'importRooms'])
            ->pushStep('imageTypes', [$this, 'importImageTypes'])
            ->pushStep('terminals', [$this, 'importTerminals'])
            ->pushStep('hotels', [$this, 'importHotels'])
        ;
    }

    /**
     * @param StaticDataCriteriaInterface $criteria
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importCountries(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Country::class);
        switch ((string)$criteria->getLocale()) {
            case 'en_US':
                $collection
                    ->attach(new ImportDTO('dummy_ad', ['isoCode' => 'AD'], (new TranslatableString())->setValue('Andorra')->setLocale(new Locale('en'))->setEntityField('name')))
                    ->attach(new ImportDTO('dummy_ae', ['isoCode' => 'AE'], (new TranslatableString())->setValue('United Arab Emirates')->setLocale(new Locale('en'))->setEntityField('name')));
                break;

            case 'ru_UA':
                $collection
                    ->attach(new ImportDTO('dummy_ad', ['isoCode' => 'AD'], (new TranslatableString())->setValue('Андорра')->setLocale(new Locale('ru'))->setEntityField('name')))
                    ->attach(new ImportDTO('dummy_ae', ['isoCode' => 'AE'], (new TranslatableString())->setValue('ОАЭ')->setLocale(new Locale('ru'))->setEntityField('name')));
                break;

            default: // Mixed translates
                $collection
                    ->attach(new ImportDTO('dummy_ad', ['isoCode' => 'AD'],
                            (new TranslatableString())->setValue('Andorra')->setLocale(new Locale('en'))->setEntityField('name'),
                            (new TranslatableString())->setValue('Андорра')->setLocale(new Locale('ru'))->setEntityField('name'))
                    )
                    ->attach(new ImportDTO('dummy_ae', ['isoCode' => 'AE'],
                        (new TranslatableString())->setValue('United Arab Emirates')->setLocale(new Locale('en'))->setEntityField('name'),
                        (new TranslatableString())->setValue('ОАЭ')->setLocale(new Locale('ru'))->setEntityField('name')
                    ));

        }

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importDestinations(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        // Fist bulk
        $importDataService->import(
            (new StaticDataCollection(Destination::class))
                ->attach(new ImportDTO('dummy_AUH', ['country' => 'dummy_ae', 'rootDestination' => null], (new TranslatableString())->setValue('Abu Dhabi')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AUH:Z:1', ['country' => 'dummy_ae', 'rootDestination' => 'dummy_AUH', 'containedInTheDestinations' => ['dummy_AUH']], (new TranslatableString())->setValue('Abu Dhabi')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AUH:Z:2', ['country' => 'dummy_ae', 'rootDestination' => 'dummy_AUH', 'containedInTheDestinations' => ['dummy_AUH']], (new TranslatableString())->setValue('Gayathi')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AUH:G:EMIRATOS1', ['country' => 'dummy_ae', 'rootDestination' => 'dummy_AUH', 'containsDestinations' => ['dummy_AUH:Z:1'], 'containedInTheDestinations' => ['dummy_AUH']], (new TranslatableString())->setValue('Abu Dhabi area')->setLocale(new Locale('en'))->setEntityField('name')))
        );

        // Second bulk
        $importDataService->import(
            (new StaticDataCollection(Destination::class))
                ->attach(new ImportDTO('dummy_AND', ['country' => 'dummy_ad', 'rootDestination' => null], (new TranslatableString())->setValue('Andorra')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:5', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Pas de la Casa')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:10', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Soldeu')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:15', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Encamp')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:20', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Canillo')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:25', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Andorra la Vella')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:30', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('La Massana')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:35', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Ordino')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:40', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Arinsal')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:45', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Ransol')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:50', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Escaldes')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:55', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('El Tarter')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:60', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Grau Roig')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:Z:99', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('Sant Julia')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:G:ANDORAND36', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containsDestinations' => ['dummy_AND:Z:25', 'dummy_AND:Z:50', 'dummy_AND:Z:99'], 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('ANDORRA LA VELLA-ESCALDES-SANT JULIA')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:G:ENCAMAND6', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containsDestinations' => ['dummy_AND:Z:15'], 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('ENCAMP')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:G:LAMASAND18', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containsDestinations' => ['dummy_AND:Z:30', 'dummy_AND:Z:40'], 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('LA MASSANA-ARINSAL')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:G:ORDINAND6', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containsDestinations' => ['dummy_AND:Z:35'], 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('ORDINO')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:G:PASDEAND14', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containsDestinations' => ['dummy_AND:Z:5', 'dummy_AND:Z:60'], 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('PAS DE LA CASA')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('dummy_AND:G:SOLDEAND21', ['country' => 'dummy_ad', 'rootDestination' => 'dummy_AND', 'containsDestinations' => ['dummy_AND:Z:10', 'dummy_AND:Z:20', 'dummy_AND:Z:45', 'dummy_AND:Z:55'], 'containedInTheDestinations' => ['dummy_AND']], (new TranslatableString())->setValue('SOLDEU-RANSOL-CANILLO')->setLocale(new Locale('en'))->setEntityField('name')))
        );

        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importAccommodations(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Accommodation::class);

        $collection
            ->attach(new ImportDTO('CAMPING', [], (new TranslatableString())->setValue('Camping')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('HOMES', [], (new TranslatableString())->setValue('Villa')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('HOSTEL', [], (new TranslatableString())->setValue('Hostel')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('HOTEL', [], (new TranslatableString())->setValue('Hotel')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('APART', [], (new TranslatableString())->setValue('Apartment')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('APTHOTEL', [], (new TranslatableString())->setValue('Aparthotel')->setLocale(new Locale('en'))->setEntityField('name')));

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importBoards(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Board::class);

        $collection
            ->attach(new ImportDTO('AB', ['code' => 'AB'], (new TranslatableString())->setValue('AMERICAN BREAKFAST')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('AI', ['code' => 'AI'], (new TranslatableString())->setValue('ALL INCLUSIVE')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('AS', ['code' => 'AS'], (new TranslatableString())->setValue('ALL INCLUSIVE SPECIAL')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('BB', ['code' => 'BB'], (new TranslatableString())->setValue('BED AND BREAKFAST')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('BH', ['code' => 'BH'], (new TranslatableString())->setValue('1 BED AND BREAKFAST  +1 HALF BOARD')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('CB', ['code' => 'CB'], (new TranslatableString())->setValue('CONTINENTAL BREAKFAST')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('HB', ['code' => 'HB'], (new TranslatableString())->setValue('HALF BOARD')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('RO', ['code' => 'RO'], (new TranslatableString())->setValue('ROOM ONLY')->setLocale(new Locale('en'))->setEntityField('name')))
        ;

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importCategories(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Category::class);

        $collection
            ->attach(new ImportDTO('1EST:GROUP01', ['simple' => 1, 'accommodation' => 'HOTEL', 'group' => 'GROUP01'], (new TranslatableString())->setValue('1 STAR')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('1LL:GROUP07', ['simple' => 1, 'accommodation' => 'HOSTEL', 'group' => 'GROUP07'], (new TranslatableString())->setValue('1 KEY')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('2EST:GROUP02', ['simple' => 2, 'accommodation' => 'HOTEL', 'group' => 'GROUP02'], (new TranslatableString())->setValue('2 STARS')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('2LL:GROUP07', ['simple' => 2, 'accommodation' => 'HOMES', 'group' => 'GROUP07'], (new TranslatableString())->setValue('2 KEYS')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('3EST:GROUP03', ['simple' => 3, 'accommodation' => 'HOSTEL', 'group' => 'GROUP03'], (new TranslatableString())->setValue('3 STARS')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('3LL:GROUP07', ['simple' => 3, 'accommodation' => 'CAMPING', 'group' => 'GROUP07'], (new TranslatableString())->setValue('3 KEYS')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('4EST:GRUPO4', ['simple' => 4, 'accommodation' => 'CAMPING', 'group' => 'GROUP04'], (new TranslatableString())->setValue('4 STARS')->setLocale(new Locale('en'))->setEntityField('name')))
        ;

        $importDataService->import($collection);
        return $this;
    }


    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importSegments(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Segment::class);

        $collection
            ->attach(new ImportDTO('dummy_31', [], (new TranslatableString())->setValue('Design')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('dummy_34', [], (new TranslatableString())->setValue('Business hotels')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('dummy_36', [], (new TranslatableString())->setValue('Golf hotels')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('dummy_37', [], (new TranslatableString())->setValue('Beach hotels')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('dummy_39', [], (new TranslatableString())->setValue('Hotels with spa')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('dummy_42', [], (new TranslatableString())->setValue('Historical')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('dummy_43', [], (new TranslatableString())->setValue('Ski hotels')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('dummy_81', [], (new TranslatableString())->setValue('Family hotels')->setLocale(new Locale('en'))->setEntityField('name')))
        ;

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importChains(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        // Fist bulk
        $importDataService->import(
            (new StaticDataCollection(Chain::class))
                ->attach(new ImportDTO('13CO', [], (new TranslatableString())->setValue('13 COINS HOTELS')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('1834', [], (new TranslatableString())->setValue('1834 HOTELS')->setLocale(new Locale('en'))->setEntityField('name'))));

        // Second bulk
        $importDataService->import(
            (new StaticDataCollection(Chain::class))
                ->attach(new ImportDTO('3HB', ['code' => '3HB'], (new TranslatableString())->setValue('3hb hotels')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('3K', ['code' => '3K'], (new TranslatableString())->setValue('3K')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('4RHOT', ['code' => '4RHOT'], (new TranslatableString())->setValue('4RHotels')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('7DAYS', ['code' => '7DAYS'], (new TranslatableString())->setValue('7 DAYS INN')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('HOTAN', ['code' => 'HOTAN'], (new TranslatableString())->setValue('HOTANSA')->setLocale(new Locale('en'))->setEntityField('name')))
        );
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importCurrencies(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Currency::class);
        $collection
            ->attach(new ImportDTO('AED', ['isoCode' => 'AED', 'type' => 'LIBERATE'], (new TranslatableString())->setValue('Utd. Arab Emir. Dirham')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('AFA', ['isoCode' => 'AFA', 'type' => 'LIBERATE'], (new TranslatableString())->setValue('AFGHANI')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('ALL', ['isoCode' => 'ALL', 'type' => 'LIBERATE'], (new TranslatableString())->setValue('LEK')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('AMD', ['isoCode' => 'AMD', 'type' => 'LIBERATE'], (new TranslatableString())->setValue('ARMENIAN DRAM')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('ANG', ['isoCode' => 'ANG', 'type' => 'LIBERATE'], (new TranslatableString())->setValue('ANTIL. GUILDER')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('AOR', ['isoCode' => 'AOR', 'type' => 'LIBERATE'], (new TranslatableString())->setValue('WANZA REAJUST.')->setLocale(new Locale('en'))->setEntityField('name')));

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param StaticDataCriteriaInterface $criteria
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importFacilityGroups(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(FacilityGroup::class);
        $collection->attach(new ImportDTO('10', [], (new TranslatableString())->setValue('Location')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('20', [], (new TranslatableString())->setValue('Hotel type')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('30', [], (new TranslatableString())->setValue('Methods of payment')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('40', [], (new TranslatableString())->setValue('Distances (in meters)')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('60', [], (new TranslatableString())->setValue('Distances (in meters)')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('61', [], (new TranslatableString())->setValue('HotelRoom Distribution')->setLocale(new Locale('en'))->setEntityField('name')));

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importFacilityTypologies(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(FacilityTypology::class);
        $collection
            ->attach(new ImportDTO('2', ['number' => true, 'logic' => false, 'fee' => false, 'distance' => false, 'ageFrom' => false, 'ageTo' => false, 'dateFrom' => false, 'dateTo' => false, 'timeFrom' => false, 'timeTo' => false, 'indYesOrNo' => false, 'amount' => false, 'currency' => false, 'appType' => false, 'text' => false]))
            ->attach(new ImportDTO('3', ['number' => false, 'logic' => true, 'fee' => false, 'distance' => false, 'ageFrom' => false, 'ageTo' => false, 'dateFrom' => false, 'dateTo' => false, 'timeFrom' => false, 'timeTo' => false, 'indYesOrNo' => false, 'amount' => false, 'currency' => false, 'appType' => false, 'text' => false]))
            ->attach(new ImportDTO('5', ['number' => false, 'logic' => true, 'fee' => true, 'distance' => false, 'ageFrom' => false, 'ageTo' => false, 'dateFrom' => false, 'dateTo' => false, 'timeFrom' => false, 'timeTo' => false, 'indYesOrNo' => false, 'amount' => false, 'currency' => false, 'appType' => false, 'text' => false]))
            ->attach(new ImportDTO('8', ['number' => true, 'logic' => true, 'fee' => false, 'distance' => false, 'ageFrom' => false, 'ageTo' => false, 'dateFrom' => false, 'dateTo' => false, 'timeFrom' => false, 'timeTo' => false, 'indYesOrNo' => false, 'amount' => false, 'currency' => false, 'appType' => false, 'text' => false]))
            ->attach(new ImportDTO('12', ['number' => false, 'logic' => true, 'fee' => true, 'distance' => false, 'ageFrom' => false, 'ageTo' => false, 'dateFrom' => false, 'dateTo' => false, 'timeFrom' => false, 'timeTo' => false, 'indYesOrNo' => false, 'amount' => false, 'currency' => false, 'appType' => false, 'text' => false]))
            ->attach(new ImportDTO('14', ['number' => false, 'logic' => false, 'fee' => false, 'distance' => false, 'ageFrom' => false, 'ageTo' => false, 'dateFrom' => false, 'dateTo' => false, 'timeFrom' => false, 'timeTo' => false, 'indYesOrNo' => false, 'amount' => false, 'currency' => false, 'appType' => false, 'text' => true]))
            ->attach(new ImportDTO('20', ['number' => true, 'logic' => false, 'fee' => false, 'distance' => false, 'ageFrom' => false, 'ageTo' => false, 'dateFrom' => false, 'dateTo' => false, 'timeFrom' => false, 'timeTo' => false, 'indYesOrNo' => true, 'amount' => false, 'currency' => false, 'appType' => false, 'text' => false]))
        ;

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importFacilities(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Facility::class);

        $collection
            ->attach(new ImportDTO('1:10', ['group' => '10', 'typology' => '5'], (new TranslatableString())->setValue('Single bed 90-130 width')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('1:20', ['group' => '20', 'typology' => '14'], (new TranslatableString())->setValue('Single bed 90-130 width')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('10:30', ['group' => '30', 'typology' => '8'], (new TranslatableString())->setValue('Gay-friendly')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('10:40', ['group' => '40', 'typology' => '5'], (new TranslatableString())->setValue('Hotel')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('10:60', ['group' => '60', 'typology' => '8'], (new TranslatableString())->setValue('American Express')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('10:61', ['group' => '61', 'typology' => '12'], (new TranslatableString())->setValue('City centre')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('295:60', ['group' => '60', 'typology' => '20'], (new TranslatableString())->setValue('Room size (sqm)')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('298:60', ['group' => '60', 'typology' => '20'], (new TranslatableString())->setValue('Number of bedrooms')->setLocale(new Locale('en'))->setEntityField('name')))
        ;


        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importIssues(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Issue::class);
        $collection
            ->attach(new ImportDTO('PROHIBITED', ['type' => 'ALCOHOLPROHIB', 'alternative' => true], (new TranslatableText())->setValue('No alcohol will be served in the hotel shops nor will it be provided by room service.')->setLocale(new Locale('en'))->setEntityField('description'), (new TranslatableText())->setValue('Alcohol is prohibited to be served both day and night in any outlets within the hotel or in room service.')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('ARRIVALTIME', ['type' => 'ARRIVALTIME', 'alternative' => false], (new TranslatableText())->setValue('Clients should notify their arrival time to the hotel.')->setLocale(new Locale('en'))->setEntityField('description'), (new TranslatableText())->setValue('Clients should notify their arrival time to the hotel.')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('ELECTRONICTRAVE', ['type' => 'ELECTRONICTRAVE', 'alternative' => false]))
            ->attach(new ImportDTO('CASH', ['type' => 'CASH', 'alternative' => true], (new TranslatableText())->setValue('A credit card is required to guarantee the booking but the hotel only accepts cash as a method of payment.\n')->setLocale(new Locale('en'))->setEntityField('description')))
            ->attach(new ImportDTO('BAR', ['type' => 'CLOSED', 'alternative' => true], (new TranslatableText())->setValue('The bar is closed.')->setLocale(new Locale('en'))->setEntityField('description')))
            ->attach(new ImportDTO('BEACH', ['type' => 'CLOSED', 'alternative' => true], (new TranslatableText())->setValue('Closed access to the beach.')->setLocale(new Locale('en'))->setEntityField('description')));

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importPromotions(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(Promotion::class);

        $collection
            ->attach(new ImportDTO('013', [], (new TranslatableString())->setValue('Board upgrade applied')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('014', [], (new TranslatableString())->setValue('Transfer included')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('022', [], (new TranslatableString())->setValue('Breakfast offer')->setLocale(new Locale('en'))->setEntityField('name')));

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importRooms(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $importDataService->import(
            (new StaticDataCollection(Room::class))
                ->attach(new ImportDTO('APT.1B', ['type' => 'APT', 'characteristic' => '1B', 'minPax' => 0, 'maxPax' => 5, 'maxAdults' => 5, 'minAdults' => 1, 'maxChildren' => 3], (new TranslatableText())->setValue('ONE BED')->setLocale(new Locale('en'))->setEntityField('description')))
                ->attach(new ImportDTO('APT.1B-1', ['type' => 'APT', 'characteristic' => '1B-1', 'minPax' => 1, 'maxPax' => 7, 'maxAdults' => 6, 'minAdults' => 1, 'maxChildren' => 3], (new TranslatableText())->setValue('ONE BED')->setLocale(new Locale('en'))->setEntityField('description')))
                ->attach(new ImportDTO('DBT.ST', ['type' => 'DBT', 'characteristic' => 'ST', 'minPax' => 1, 'maxPax' => 2, 'maxAdults' => 2, 'minAdults' => 1, 'maxChildren' => 1], (new TranslatableText())->setValue('Double or Twin ONE BED')->setLocale(new Locale('en'))->setEntityField('description')))
                ->attach(new ImportDTO('TPL.ST', ['type' => 'TPL', 'characteristic' => 'ST', 'minPax' => 1, 'maxPax' => 3, 'maxAdults' => 3, 'minAdults' => 1, 'maxChildren' => 2], (new TranslatableText())->setValue('Twin or Triple TWO BED')->setLocale(new Locale('en'))->setEntityField('description')))
        );

        $importDataService->import(
            (new StaticDataCollection(Room::class))
                ->attach(new ImportDTO('APT.1B-DX', ['code' => 'APT.1B-DX', 'type' => 'APT', 'characteristic' => '1B-DX', 'minPax' => 1, 'maxPax' => 5, 'maxAdults' => 5, 'minAdults' => 1, 'maxChildren' => 2], (new TranslatableText())->setValue('DELUXE ONE BED')->setLocale(new Locale('en'))->setEntityField('description')))
                ->attach(new ImportDTO('APT.1W-B1', ['type' => 'APT', 'characteristic' => '1W-B1', 'minPax' => 1, 'maxPax' => 5, 'maxAdults' => 5, 'minAdults' => 1, 'maxChildren' => 4], (new TranslatableString())->setValue('APARTMENT')->setLocale(new Locale('en'))->setEntityField('name')))
                ->attach(new ImportDTO('APT.1W-B2', ['type' => 'APT', 'characteristic' => '1W-B2', 'minPax' => 1, 'maxPax' => 6, 'maxAdults' => 6, 'minAdults' => 1, 'maxChildren' => 4], (new TranslatableText())->setValue('TWO BEDROOMS ONE BATHROOM')->setLocale(new Locale('en'))->setEntityField('description')))
                ->attach(new ImportDTO('APT.1W-B3', ['type' => 'APT', 'characteristic' => '1W-B3', 'minPax' => 1, 'maxPax' => 7, 'maxAdults' => 7, 'minAdults' => 1, 'maxChildren' => 1], (new TranslatableText())->setValue('THREE BEDROOMS ONE BATHROOM')->setLocale(new Locale('en'))->setEntityField('description'))));

        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importImageTypes(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $collection = new StaticDataCollection(ImageType::class);
        $collection
            ->attach(new ImportDTO('BAR', [], (new TranslatableString())->setValue('Bar')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('COM', [], (new TranslatableString())->setValue('Lobby')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('CON', [], (new TranslatableString())->setValue('Conferences')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('DEP', [], (new TranslatableString())->setValue('Sports and Entertainment')->setLocale(new Locale('en'))->setEntityField('name')))
            ->attach(new ImportDTO('GEN', [], (new TranslatableString())->setValue('General view')->setLocale(new Locale('en'))->setEntityField('name')));

        $importDataService->import($collection);
        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importTerminals(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $importDataService->import(
            (new StaticDataCollection(Terminal::class))
                                ->attach(new ImportDTO('AAE', ['isoCode' => 'AAE', 'type' => 'A', 'country' => 'dummy_ad'], (new TranslatableText())->setValue('Airport')->setLocale(new Locale('ru'))->setEntityField('description'), (new TranslatableString())->setValue('Аннаба, межд. аэропорт Рабаха Битата')->setLocale(new Locale('en'))->setEntityField('name')))
                                ->attach(new ImportDTO('AAGT', ['isoCode' => 'AAGT', 'type' => 'A', 'country' => 'dummy_ae'], (new TranslatableText())->setValue('Airport')->setLocale(new Locale('en'))->setEntityField('description'), (new TranslatableString())->setValue('AGAETE')->setLocale(new Locale('en'))->setEntityField('name'))));
        $importDataService->import(
            (new StaticDataCollection(Terminal::class))
                                ->attach(new ImportDTO('AAL', ['isoCode' => 'AAL', 'type' => 'A', 'country' => 'dummy_ad'], (new TranslatableText())->setValue('Airport')->setLocale(new Locale('ru'))->setEntityField('description'), (new TranslatableString())->setValue('Ольборг, Аэропорт Ольборг')->setLocale(new Locale('ru'))->setEntityField('name')))
                                ->attach(new ImportDTO('AAM', ['isoCode' => 'AAM', 'type' => 'A', 'country' => 'dummy_ae'], (new TranslatableText())->setValue('Airport')->setLocale(new Locale('en'))->setEntityField('description'), (new TranslatableString())->setValue('MALA MALA')->setLocale(new Locale('en'))->setEntityField('name')))
                                ->attach(new ImportDTO('AAR', ['isoCode' => 'AAR', 'type' => 'A', 'country' => 'dummy_ae'], (new TranslatableText())->setValue('Airport')->setLocale(new Locale('ru'))->setEntityField('description'), (new TranslatableString())->setValue('Орхус, Аэропорт Орхус')->setLocale(new Locale('ru'))->setEntityField('name')))
                                ->attach(new ImportDTO('ABC', ['isoCode' => 'ABC', 'type' => 'A', 'country' => 'dummy_ad'], (new TranslatableText())->setValue('Airport')->setLocale(new Locale('ru'))->setEntityField('description'), (new TranslatableString())->setValue('Альбасете, аэропорт Лос-Льянос')->setLocale(new Locale('ru'))->setEntityField('name'))));

        return $this;
    }

    /**
     * @param ImportDataService $importDataService
     * @return DummyServiceProvider
     * @throws \Exception
     */
    public function importHotels(StaticDataCriteriaInterface $criteria, ImportDataService $importDataService): DummyServiceProvider
    {
        $importDataService->import(
            (new StaticDataCollection(Hotel::class))
                ->attach(new ImportDTO(171, [
                    'country' => 'dummy_ad',
                    'destination' => 'dummy_AND:Z:10',
                    'coordinates' => new Coordinates(42.576972, 1.667054),
                    'category' => '4EST:GRUPO4',
                    'chain' => 'HOTAN',
                    'accommodationType' => 'HOTEL',
                    'postalCode' => 'AD 100',
                    'email' => 'hotelhimalaiasoldeu@andorra.ad',
                    'web' => 'http://www.hotelhimalaiasoldeu.com/',
                    'S2C' => '3*',

                    // Пример простых коллекций ссылок на сущнссти
                    'boards' => ['BB', 'HB', 'RO'],
                    'segments' => ['dummy_39', 'dummy_43', 'dummy_81'],

                    // Коллекции для сущностей которых в данный момент возможно не существет и их нужно создать или обновить
                    'phones' => (new StaticDataCollection(HotelPhone::class))
                        ->attach(new ImportDTO('H171:P:PHONEBOOKING', ['phone' => '00376878515', 'type' => 'PHONEBOOKING']))
                        ->attach(new ImportDTO('H171:P:PHONEHOTEL', ['phone' => '00376878515', 'type' => 'PHONEHOTEL']))
                        ->attach(new ImportDTO('H171:P:FAXNUMBER', ['phone' => '00376878525', 'type' => 'FAXNUMBER'])),

                    'rooms' => (new StaticDataCollection(HotelRoom::class))
                        ->attach(new ImportDTO('H171:R:DBT.ST', [
                            'type' => 'DBT.ST',
                            'facilities' => (new StaticDataCollection(HotelRoomFacility::class))
                                ->attach(new ImportDTO('H171:R:DBT.ST:F:298:60', ['facility' => '298:60', 'number' => 1, 'indYesOrNo' => true]))
                                ->attach(new ImportDTO('H171:R:DBT.ST:F:295:60', ['facility' => '295:60', 'number' => 25, 'indYesOrNo' => true])),
                            'stays' => (new StaticDataCollection(HotelRoomStay::class))
                                ->attach(new ImportDTO('H171:R:DBT.ST:S:BED', ['type' => 'BED', 'order' => 1], (new TranslatableString())->setValue('Bed room')->setLocale(new Locale('en'))->setEntityField('name'))),
                        ]))
                    ->attach(new ImportDTO('H171:R:TPL.ST', [
                        'type' => 'TPL.ST',
                        'facilities' => (new StaticDataCollection(HotelRoomFacility::class))
                            ->attach(new ImportDTO('H171:R:TPL.ST:F:298:60', ['facility' => '298:60', 'number' => 1, 'indYesOrNo' => true]))
                            ->attach(new ImportDTO('H171:R:TPL.ST:F:295:60', ['facility' => '295:60', 'number' => 25, 'indYesOrNo' => true])),
                        'stays' => (new StaticDataCollection(HotelRoomStay::class))
                            ->attach(new ImportDTO('H171:R:TPL.ST:S:BED', ['order' => 1], (new TranslatableString())->setValue('Bed room')->setLocale(new Locale('en'))->setEntityField('name'))),
                    ])),
                    'facilities' => (new StaticDataCollection(HotelFacility::class))
                        ->attach(new ImportDTO('H171:FH:10:30', ['facility' => '10:30', 'order' => 1, 'number' => 1996]))
                        ->attach(new ImportDTO('H171:FH:10:60', ['facility' => '10:60', 'order' => 1, 'number' => 2000])),
                    'interestPoints' => (new StaticDataCollection(HotelInterestPoint::class))
                        ->attach(new ImportDTO('H171:IP:20:10', ['facility' => '10:30', 'order' => 1, 'distance' => 2500], (new TranslatableString())->setValue('Valle de Ransol')->setLocale(new Locale('en'))->setEntityField('name'))),
                    'images' => (new StaticDataCollection(HotelImage::class))
                        ->attach(new ImportDTO('H171:I:' . base_convert(crc32('00/000171/000171a_hb_ro_005.jpg'), 10, 36), ['type' => 'BAR', 'original_url' => '00/000171/000171a_hb_ro_005.jpg', 'roomCode' => 'H171:R:DBT.ST', 'order' => 5]))
                        ->attach(new ImportDTO('H171:I:' . base_convert(crc32('00/000171/000171a_hb_f_011.jpg'), 10, 36), ['type' => 'GEN', 'original_url' => '00/000171/000171a_hb_f_011.jpg', 'order' => 11]))
                        ->attach(new ImportDTO('H171:I:' . base_convert(crc32('00/000171/000171a_hb_r_009.jpg'), 10, 36), ['type' => 'GEN', 'original_url' => '00/000171/000171a_hb_r_009.jpg', 'order' => 9])),
                    'terminals' => new StaticDataCollection(HotelTerminal::class),
                ],
                (new TranslatableString())->setValue('Hotansa Himalaya Soldeu')->setLocale(new Locale('en'))->setEntityField('name'),
                (new TranslatableText())->setValue('This apartment hotel is located in the tourist area of Soldeu, at the heart of Andorra offering impressive views out over the mountains. Commercial areas with shops, boutiques, bars and restaurants are to be found in the vicinity as well as Grandvalira ski area. Links to the public transport network are located directly in front of the hotel. This establishment offers guests a spacious foyer with a 24-hour reception desk, amongst many other facilities. The comfortable rooms come with an en suite bathroom with hairdryer and modern amenities. Guests are sure to be relaxed after their stay as this hotel offers a sauna, a solarium, steam rooms and a massage service. Guests are offered breakfast and evening meals in the form of a buffet. Furthermore, special dietary requirements can be catered for as well as the preparation of individual-specific dishes.')->setLocale(new Locale('en'))->setEntityField('description'),
                (new TranslatableText())->setValue('CARRETERA CANILLO,S/N')->setLocale(new Locale('en'))->setEntityField('address')
            ))
        );

        return $this;
    }

    /**
     * @param ServiceProviderReferenceInterface[] $hotelReferences
     * @param FilterCollectionInterface $filterCollection
     * @return PromiseInterface
     */
    public function getAvailablePrices(array $hotelReferences, FilterCollectionInterface $filterCollection): PromiseInterface
    {
        // Todo
    }

    public function checkRate(string $rateKey): ?ImportDTO
    {
        // TODO: Implement checkRate() method.
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException|RuntimeException
     */
    private function checkEnvironment(): void
    {
        if (!$this->container) {
            throw new RuntimeException(
                sprintf('Cannot use "%s" without container', \get_class($this))
            );
        }

        if ($this->container->getParameter('kernel.environment') !== 'dev') {
            throw new RuntimeException(
                sprintf('Cannot use "%s" on "%s" environment', \get_class($this), $this->container->getParameter('kernel.environment'))
            );
        }
    }
}