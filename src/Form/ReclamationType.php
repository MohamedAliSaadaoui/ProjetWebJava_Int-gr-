<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_name', TextType::class, [
                'label' => 'Your Name',
                'mapped' => false, // Not mapped to the entity
                'attr' => [
                    'readonly' => true, // Prevent user from editing
                    'placeholder' => $options['user_name'] ?? 'Guest', // Set default if user_name is missing
                ],
            ])
            ->add('objet', TextType::class, ['label' => 'Subject'])

            ->add('description', TextareaType::class, ['label' => 'Message'])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'Product Issue' => 'product_issue',
                    'Delivery Problem' => 'delivery_problem',
                    'Payment Issue' => 'payment_issue',
                    'Other' => 'other',
                ],
                'placeholder' => 'Select a category',
            ])
            ->add('attachments', FileType::class, [
                'label' => 'Attachments',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'user_name' => null, // Define the user_name option properly
        ]);
    }
}
