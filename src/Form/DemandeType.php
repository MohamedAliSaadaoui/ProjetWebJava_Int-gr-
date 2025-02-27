<?php

namespace App\Form;

use App\Entity\Demande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('localisation')
            ->add('categorie', ChoiceType::class, [ // 👈 Utilisez ChoiceType
                'choices' => [
                    'Vêtements' => 'Vêtements', // 👈 Options de la liste déroulante
                    'Chaussures' => 'Chaussures',
                    'Autres' => 'Autres',
                ],
                'placeholder' => 'Choisir une catégorie...', // 👈 Texte par défaut
                'label' => 'Catégorie', // 👈 Libellé du champ
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}
