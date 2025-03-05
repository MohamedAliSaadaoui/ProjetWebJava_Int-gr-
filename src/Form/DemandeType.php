<?php

namespace App\Form;

use App\Entity\Demande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(['message' => 'La description ne peut pas être vide.']),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'constraints' => [
                    new NotBlank(['message' => 'La localisation est obligatoire.']),
                ],
            ])
            ->add('categorie', ChoiceType::class, [
                'choices' => [
                    'Vêtements' => 'Vêtements',
                    'Chaussures' => 'Chaussures',
                    'Autres' => 'Autres',
                ],
                'placeholder' => 'Choisir une catégorie...',
                'label' => 'Catégorie',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir une catégorie.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}
