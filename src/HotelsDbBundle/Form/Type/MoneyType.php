<?php


namespace Apl\HotelsDbBundle\Form\Type;


use Apl\HotelsDbBundle\Entity\Money\Money;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Class MoneyType
 *
 * @package Apl\HotelsDbBundle\Form\Type
 */
class MoneyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 16,
                    ]),
                    new Regex([
                        'pattern' => "/^[0-9\.]+$/",
                        'message' => 'Incorrect amount format',
                    ])
                ]
            ])
            ->add('currency', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[A-Z]{3}$/i',
                        'message' => 'Incorrect currency format',
                    ])
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Money::class,
        ]);
    }
}