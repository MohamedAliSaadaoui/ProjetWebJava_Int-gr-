<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text', // Affiche un seul champ de saisie de date
                'html5' => true, // Utilise le type HTML5 "date"
                'attr' => ['class' => 'form-control'], // Ajoute une classe CSS
            ])
            ->add('dateFin', DateType::class, [ // Use the correct DateType
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('lieu')
            ->add('statut',ChoiceType::class, [
                'choices' => [
                    'Upcoming' => 'upcoming',
                    'Ongoing' => 'ongoing',
                    'Cancelled' => 'cancelled',
                ],
                'placeholder' => 'Select a status',
                'required' => true,
            ])

            ->add('categorie',ChoiceType::class, [
                'choices' => Event::CATEGORIES,
                'placeholder' => 'Choose a category', // Optionnel : texte par défaut
                'attr' => ['class' => 'form-control'],
            ]);
        
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
