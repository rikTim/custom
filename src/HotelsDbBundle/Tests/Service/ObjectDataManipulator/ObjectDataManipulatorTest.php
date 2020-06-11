<?php

namespace Apl\HotelsDbBundle\Tests\Service\ObjectHydrator;


use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Comparator\EqualComparator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasSetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\HasGetterMappingInterface;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Hydrator\ScalarHydrator;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterAttribute;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\SetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\Mapping\GetterMapping;
use Apl\HotelsDbBundle\Service\ObjectDataManipulator\ObjectDataManipulator;

class ObjectDataManipulatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group object_data_manipulator
     */
    public function testHydrate()
    {
        $service = new ObjectDataManipulator();
        $service->addHydrator(new ScalarHydrator());

        // Configure destination
        $objectDst = $this->getMockForAbstractClass(HasSetterMappingInterface::class, [], '', false, false, true, [
            'getSetterMapping', 'setTestAttributeString', 'setTestAttributeInteger', 'customSetter'
        ]);
        $objectDst->expects($this->once())->method('getSetterMapping')->willReturn(new SetterMapping(
            ScalarHydrator::getStringAttribute('testAttributeString'),
            ScalarHydrator::getIntegerAttribute('testAttributeInteger'),
            ScalarHydrator::getBooleanAttribute('customAttribute', 'customSetter')
        ));
        $objectDst->expects($this->once())->method('setTestAttributeString')->with('test string');
        $objectDst->expects($this->once())->method('setTestAttributeInteger')->with(42);
        $objectDst->expects($this->once())->method('customSetter')->with(true);

        // Configure source
        $objectSrc = $this->getMockForAbstractClass(HasGetterMappingInterface::class, [], '', false, false, true, [
            'getGetterMapping', 'getTestAttributeString', 'getTestAttributeInteger', 'customGetter'
        ]);
        $objectSrc->expects($this->once())->method('getGetterMapping')->willReturn(new GetterMapping(
            new GetterAttribute('testAttributeString'),
            new GetterAttribute('testAttributeInteger'),
            new GetterAttribute('customAttribute', 'customGetter')
        ));
        $objectSrc->expects($this->once())->method('getTestAttributeString')->willReturn(new class {
            public function __toString()
            {
                return 'test string';
            }
        });
        $objectSrc->expects($this->once())->method('getTestAttributeInteger')->willReturn('42');
        $objectSrc->expects($this->once())->method('customGetter')->willReturn(1);

        $service->hydrate($objectDst, $objectSrc);

    }

    /**
     * @group object_data_manipulator
     */
    public function testCompare()
    {
        $service = new ObjectDataManipulator();
        $service->addComparator(new EqualComparator());

        // Configure dst
        $getterMapping = new GetterMapping(
            new GetterAttribute('testAttributeEqual', null, EqualComparator::class),
            new GetterAttribute('testAttributeSame')
        );

        $objectDst = $this->getMockForAbstractClass(HasGetterMappingInterface::class, [], '', false, false, true, [
            'getGetterMapping', 'getTestAttributeEqual', 'getTestAttributeSame'
        ]);
        $objectDst->expects($this->exactly(2))->method('getGetterMapping')->willReturn($getterMapping);
        $objectDst->expects($this->exactly(2))->method('getTestAttributeEqual')->willReturn('1');
        $objectDst->expects($this->exactly(2))->method('getTestAttributeSame')->willReturn('1');

        $objectSrc = $this->getMockForAbstractClass(HasGetterMappingInterface::class, [], '', false, false, true, [
            'getGetterMapping', 'getTestAttributeEqual', 'getTestAttributeSame'
        ]);
        $objectSrc->expects($this->once())->method('getGetterMapping')->willReturn($getterMapping);
        $objectSrc->expects($this->once())->method('getTestAttributeEqual')->willReturn(true);
        $objectSrc->expects($this->once())->method('getTestAttributeSame')->willReturn(true);

        $this->assertFalse($service->compare($objectDst, $objectSrc));

        $objectSrc = $this->getMockForAbstractClass(HasGetterMappingInterface::class, [], '', false, false, true, [
            'getGetterMapping', 'getTestAttributeEqual', 'getTestAttributeSame'
        ]);
        $objectSrc->expects($this->once())->method('getGetterMapping')->willReturn($getterMapping);
        $objectSrc->expects($this->once())->method('getTestAttributeEqual')->willReturn(true);
        $objectSrc->expects($this->once())->method('getTestAttributeSame')->willReturn('1');

        $this->assertTrue($service->compare($objectDst, $objectSrc));
    }
}
