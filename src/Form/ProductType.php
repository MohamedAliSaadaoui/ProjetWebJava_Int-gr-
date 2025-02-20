<?php
namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Product Name',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a product name',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'The product name must be at least {{ limit }} characters long',
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'Clothing' => 'clothing',
                    'Electronics' => 'electronics',
                    'Home & Garden' => 'home_garden',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a category',
                    ]),
                ],
            ])
            ->add('productCondition', ChoiceType::class, [
                'label' => 'Condition',
                'choices' => [
                    'New' => 'new',
                    'Used' => 'used',
                    'Refurbished' => 'refurbished',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a product condition',
                    ]),
                ],
            ])
            ->add('color', TextType::class, [
                'label' => 'Color',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a color',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'The color must be at least {{ limit }} characters long',
                    ]),
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the stock quantity',
                    ]),
                ],
                
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a description',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Description should be at least {{ limit }} characters long',
                    ]),
                ],
            ])
            ->add('returnPolicy', TextareaType::class, [
                'label' => 'Return Policy',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a return policy',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Return policy should be at least {{ limit }} characters long',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
