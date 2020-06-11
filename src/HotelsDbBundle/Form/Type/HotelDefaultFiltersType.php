<?php


namespace Apl\HotelsDbBundle\Form\Type;


use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\BoardFilterEventSubscriber;
use Apl\HotelsDbBundle\EventSubscriber\HotelFilter\CurrencyFilterEventSubscriber;
use Apl\HotelsDbBundle\Form\DataTransformer\IntegerIdCommaSeparatedTransformer;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\EventListener\MergeCollectionListener;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HotelDefaultFiltersType
 *
 * @package HotelsBundle\Type
 * @SWG\Definition()
 */
class HotelDefaultFiltersType extends AbstractType
{
    /**
     * @var IntegerIdCommaSeparatedTransformer
     */
    protected $integerIdCommaSeparatedTransformer;

    /**
     * HotelDefaultFiltersType constructor.
     *
     * @param IntegerIdCommaSeparatedTransformer $integerIdCommaSeparatedTransformer
     */
    public function __construct(IntegerIdCommaSeparatedTransformer $integerIdCommaSeparatedTransformer)
    {
        $this->integerIdCommaSeparatedTransformer = $integerIdCommaSeparatedTransformer;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'required' => true,
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'data_class' => \DateTimeImmutable::class,
                'documentation' => [
                    'description' => 'Check-in date',
                ]
            ])
            ->add('endDate', DateType::class, [
                'required' => true,
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'data_class' => \DateTimeImmutable::class,
                'documentation' => [
                    'description' => 'Check-out date',
                ]
            ])
            ->add('adult', IntegerType::class, [
                'required' => true,
                'documentation' => [
                    'description' => 'Adult guest quantity',
                ]
            ])
            ->add('children', TextType::class, [
                'documentation' => [
                    'description' => 'Children guests ages coma separated',
                ]
            ])
            ->add(BoardFilterEventSubscriber::FILTER_KEY, TextType::class, [
                'documentation' => [
                    'description' => 'Board ids coma separated',
                ]
            ])
            ->add(CurrencyFilterEventSubscriber::FILTER_KEY, TextType::class, [
                'empty_data' => 'UAH',
                'documentation' => [
                    'type' => 'string',
                    'description' => 'Currency code (3-char)',
                ]
            ])
        ;

        $builder->get('children')
            ->addModelTransformer(new CallbackTransformer(
                function ($childrenAges) {
                    return $childrenAges ? implode(',', $childrenAges) : null;
                },
                function (?string $childrenAgesString) {
                    $childrenAges = [];
                    if ($childrenAgesString !== null) {
                        foreach (explode(',', $childrenAgesString) as $age) {
                            $childrenAges[] = $age ? min(17, max(0, (int)$age)) : 10;
                        }
                    }

                    return $childrenAges;
                }
            ));

        $builder->get(BoardFilterEventSubscriber::FILTER_KEY)
            ->addModelTransformer($this->integerIdCommaSeparatedTransformer);
    }
}