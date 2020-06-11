<?php


namespace Apl\HotelsDbBundle\Form\Type;


use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\AccommodationFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\CategoryFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\DynamicChildrenDestinationFilterEventSubscriber;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class HotelListFiltersType
 *
 * @package Apl\HotelsDbBundle\Form\Type
 */
class HotelListFiltersType extends HotelDefaultFiltersType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locationType', TextType::class, [
                'required' => true,
            ])
            ->add('locationAlias', TextType::class, [
                'required' => true,
            ])
            ->add('routeName', TextType::class, [
                'required' => true,
            ])
            ->add('query', TextType::class, [
                'required' => true,
            ])
            ->add('alias', TextType::class,[
                'required' => false
            ])
        ;

        parent::buildForm($builder, $options);

        $builder
            ->add(DynamicChildrenDestinationFilterEventSubscriber::FILTER_KEY, TextType::class, [
                'required' => false,
                'documentation' => [
                    'description' => 'Children destinations id list, comma separated',
                ],
            ])
            ->get(DynamicChildrenDestinationFilterEventSubscriber::FILTER_KEY)
            ->addModelTransformer($this->integerIdCommaSeparatedTransformer);


        $builder
            ->add(AccommodationFilterEventSubscriber::FILTER_KEY, TextType::class, [
                'required' => false,
                'documentation' => [
                    'description' => 'Children destinations id list, comma separated',
                ],
            ])
            ->get(AccommodationFilterEventSubscriber::FILTER_KEY)
            ->addModelTransformer($this->integerIdCommaSeparatedTransformer);


        $builder
            ->add(CategoryFilterEventSubscriber::FILTER_KEY, TextType::class, [
                'required' => false,
                'documentation' => [
                    'description' => 'Children destinations id list, comma separated',
                ],
            ])
            ->get(CategoryFilterEventSubscriber::FILTER_KEY)
            ->addModelTransformer($this->integerIdCommaSeparatedTransformer);
    }
}