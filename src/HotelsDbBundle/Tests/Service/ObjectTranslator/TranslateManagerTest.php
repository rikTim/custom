<?php

namespace Apl\HotelsDbBundle\Tests\Service\ObjectTranslator;


use Apl\HotelsDbBundle\Entity\Locale;
use Apl\HotelsDbBundle\Entity\Traits\HasTranslationTrait;
use Apl\HotelsDbBundle\Entity\TranslateType\TranslatableString;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\EntityManagerProxy\EntityManagerProxy;
use Apl\HotelsDbBundle\Service\LocaleDetector\LocaleDetector;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslatableObjectInterface;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateManager;
use Apl\HotelsDbBundle\Service\ObjectTranslator\TranslateTypeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TestTranslatableObject implements TranslatableObjectInterface
{
    use HasTranslationTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @return string[]
     */
    public static function getTranslateMapping(): array
    {
        return [
            'testField' => TranslatableString::class
        ];
    }

    public function __construct(int $id = null)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}

/**
 * Class TranslateManagerTest
 * @package Apl\HotelsDbBundle\Tests\Service\ObjectTranslator
 */
class TranslateManagerTest extends TestCase
{
    /**
     * @var Locale
     */
    private $currentLocale;

    /**
     * @var \Apl\HotelsDbBundle\Service\LocaleDetector\LocaleDetector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDetectorMock;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var TranslateManager
     */
    private $translateManager;

    public function setUp()
    {
        $this->currentLocale = new Locale('en_US');
        $this->localeDetectorMock = $this->createMock(LocaleDetector::class);
        $this->localeDetectorMock->expects($this->any())->method('getCurrentLocale')->willReturn($this->currentLocale);
        $this->localeDetectorMock->expects($this->any())->method('getDefaultLocale')->willReturn($this->currentLocale);

        $this->entityManagerMock = $this->createMock(EntityManagerProxy::class);

        $this->translateManager = new TranslateManager(['en']);
        $this->translateManager->setEntityManager($this->entityManagerMock);
        $this->translateManager->setLocaleDetector($this->localeDetectorMock);

    }

    /**
     * @group object_translator
     */
    public function testAttachCollection()
    {
        $translatableObject = $this->getMockForAbstractClass(TranslatableObjectInterface::class);

        $translatableObject->expects($this->never())->method('hasTranslatesCollection');
        $translatableObject->expects($this->once())->method('setTranslatesCollection');
        $this->translateManager->attachCollection($translatableObject);
    }

    /**
     * @group object_translator
     */
    public function testLoadTranslations()
    {
        // For new entity without id
        $translatableObject = $this->getMockForAbstractClass(TranslatableObjectInterface::class);

        $translatableObject->expects($this->once())->method('hasTranslatesCollection')->willReturn(false);
        $translatableObject->expects($this->once())->method('setTranslatesCollection');

        $translatableObject->expects($this->once())->method('getTranslateId')->willReturn(null);
        $translatableObject->expects($this->never())->method('getTranslatesCollection');

        $this->translateManager->loadTranslations($translatableObject);

        // For exist entity
        $translatableObject = new TestTranslatableObject(1);

        $translatableStringMock = $this->createMock(TranslatableString::class);
        $translatableStringMock->expects($this->any())->method('getLocale')->willReturn($this->currentLocale);
        $translatableStringMock->expects($this->any())->method('getEntityField')->willReturn('testField');

        $repository = $this->getMockForAbstractClass(TranslateTypeRepositoryInterface::class);
        $repository->expects($this->once())->method('findTranslate')->willReturnCallback(function(string $alias, array $id, Locale $locale = null) use ($translatableStringMock) {
            $this->assertSame(TestTranslatableObject::class, $alias);
            $this->assertSame([1], $id);
            $this->assertNull($locale);

            return [$translatableStringMock];
        });

        $this->entityManagerMock->expects($this->once())->method('getRepository')->willReturn($repository);

        $this->translateManager->loadTranslations($translatableObject);
        $this->assertSame($translatableStringMock, $translatableObject->getTranslate('testField'));
    }

    /**
     * @group object_translator
     */
    public function testLoadTranslationsForAllObjects()
    {
        $translatableCollection = [
            new TestTranslatableObject(1), new TestTranslatableObject(2), new TestTranslatableObject(null)
        ];

        $translatableStringMock = $this->createMock(TranslatableString::class);
        $translatableStringMock->expects($this->any())->method('getLocale')->willReturn($this->currentLocale);
        $translatableStringMock->expects($this->any())->method('getEntityAlias')->willReturn($translatableCollection[1]->getTranslateAlias());
        $translatableStringMock->expects($this->any())->method('getEntityField')->willReturn('testField');
        $translatableStringMock->expects($this->any())->method('getEntityId')->willReturn(2);

        $repository = $this->getMockForAbstractClass(TranslateTypeRepositoryInterface::class);
        $repository->expects($this->once())->method('findTranslate')->willReturnCallback(function(string $alias, array $id, Locale $locale = null) use ($translatableStringMock) {
            $this->assertSame(TestTranslatableObject::class, $alias);
            $this->assertSame([1, 2], $id);
            $this->assertNull($locale);

            return [$translatableStringMock];
        });

        $this->entityManagerMock->expects($this->once())->method('getRepository')->willReturn($repository);

        $this->translateManager->loadTranslationsForAllObjects($translatableCollection);
        $this->assertNotSame($translatableStringMock, $translatableCollection[0]->getTranslate('testField'));
        $this->assertSame($translatableStringMock, $translatableCollection[1]->getTranslate('testField'));
    }

    /**
     * @group object_translator
     */
    public function testPersistTranslations()
    {
        $this->entityManagerMock->expects($this->never())->method('flush');

        // With id
        $repository = $this->getMockForAbstractClass(TranslateTypeRepositoryInterface::class);
        $repository->expects($this->once())->method('findTranslate')->willReturnCallback(function(string $alias, array $id, Locale $locale = null) {
            $this->assertSame(TestTranslatableObject::class, $alias);
            $this->assertSame($this->currentLocale, $locale);

            return [];
        });

        $this->entityManagerMock->expects($this->once())->method('getRepository')->willReturn($repository);

        $translatableObject = new TestTranslatableObject(1);
        $this->translateManager->attachCollection($translatableObject);
        $translate = $translatableObject->getTranslate('testField');

        $this->entityManagerMock->expects($this->once())->method('persist')->with($translate);
        $this->translateManager->persistTranslations($translatableObject);

        // Without id
        $translatableObject2 = new TestTranslatableObject();
        $this->translateManager->attachCollection($translatableObject2);
        $translatableObject2->getTranslate('testField');
        $this->translateManager->persistTranslations($translatableObject2);
    }

    /**
     * @group object_translator
     */
    public function testPersistTranslate()
    {
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->entityManagerMock->expects($this->never())->method('remove');

        $repository = $this->getMockForAbstractClass(TranslateTypeRepositoryInterface::class);
        $repository->expects($this->once())->method('findTranslate')->willReturnCallback(function(string $alias, array $id, Locale $locale = null) {
            $this->assertSame(TestTranslatableObject::class, $alias);
            $this->assertSame($this->currentLocale, $locale);

            return [];
        });

        $this->entityManagerMock->expects($this->once())->method('getRepository')->willReturn($repository);

        // With id
        $object = new TestTranslatableObject(1);
        $this->translateManager->attachCollection($object);
        $translate = $object->getTranslate('testField');

        $this->entityManagerMock->expects($this->once())->method('persist')->with($translate);
        $this->translateManager->persistTranslate($object, $translate);
        $this->assertSame(1, $translate->getEntityId());

        // Without id
        $object = new TestTranslatableObject();
        $this->translateManager->attachCollection($object);
        $translate = $object->getTranslate('testField');
        $this->expectException(RuntimeException::class);
        $this->translateManager->persistTranslate($object, $translate);
    }

    /**
     * @group object_translator
     */
    public function testRemoveTranslations()
    {
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->entityManagerMock->expects($this->never())->method('persist');

        $translatableObject = new TestTranslatableObject(1);

        $translatableStringMock = $this->createMock(TranslatableString::class);
        $translatableStringMock->expects($this->any())->method('getEntityAlias')->willReturn($translatableObject::getTranslateAlias());
        $translatableStringMock->expects($this->any())->method('getEntityId')->willReturn(1);
        $translatableStringMock->expects($this->any())->method('getLocale')->willReturn($this->currentLocale);
        $translatableStringMock->expects($this->any())->method('getEntityField')->willReturn('testField');

        $repository = $this->getMockForAbstractClass(TranslateTypeRepositoryInterface::class);
        $repository->expects($this->once())->method('findTranslate')
            ->with(TestTranslatableObject::class, [1], null)
            ->willReturn([$translatableStringMock]);

        $this->entityManagerMock->expects($this->once())->method('getRepository')->willReturn($repository);
        $this->entityManagerMock->expects($this->once())->method('remove')->with($translatableStringMock);

        $this->translateManager->removeTranslations($translatableObject);
    }

    /**
     * @group object_translator
     */
    public function testRemoveTranslate()
    {
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('getRepository');


        $translatableStringMock = $this->createMock(TranslatableString::class);
        $this->entityManagerMock->expects($this->once())->method('remove')->with($translatableStringMock);
        $this->translateManager->removeTranslate($translatableStringMock);
    }

    /**
     * @group object_translator
     */
    public function testFlush()
    {
        $this->entityManagerMock->expects($this->exactly(2))->method('flush');

        // Without id
        $object = new TestTranslatableObject();
        $this->translateManager->attachCollection($object);
        $translate = $object->getTranslate('testField');
        $this->translateManager->persistTranslations($object);

        // Added id
        $object->setId(1);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($translate);

        $this->translateManager->flush();

        // Второй раз не должен вызываться персист
        $this->translateManager->flush();
    }
}
