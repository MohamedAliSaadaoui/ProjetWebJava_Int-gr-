<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('objet', TextType::class, [
                'label' => 'Objet',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Saisissez l\'objet de votre réclamation']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Décrivez votre problème']
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Produit défectueux' => 'Produit défectueux',
                    'Livraison en retard' => 'Livraison en retard',
                    'Service client' => 'Service client',
                    'Autre' => 'Autre'
                ],
                'placeholder' => 'Sélectionnez une catégorie',
                'attr' => ['class' => 'form-control']
            ])
            ->add('attachments', FileType::class, [
                'label' => 'Preuve(s)',
                'multiple' => true,  // ✅ Allow multiple files
                'mapped' => false,   // ✅ Prevent direct mapping to entity (handled manually in controller)
                'attr' => ['class' => 'form-control', 'accept' => 'image/*'], // Accept images only
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'user_name' => null,
        ]);
    }
}
