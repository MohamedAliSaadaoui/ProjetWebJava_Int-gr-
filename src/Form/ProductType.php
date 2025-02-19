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

class ProductTypeControler extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Product Name',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'Clothing' => 'clothing',
                    'Electronics' => 'electronics',
                    'Home & Garden' => 'home_garden',
                ],
            ])
            ->add('condition', ChoiceType::class, [
                'label' => 'Condition',
                'choices' => [
                    'New' => 'new',
                    'Used' => 'used',
                    'Refurbished' => 'refurbished',
                ],
            ])
            ->add('color', TextType::class, [
                'label' => 'Color',
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('returnPolicy', TextareaType::class, [
                'label' => 'Return Policy',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
