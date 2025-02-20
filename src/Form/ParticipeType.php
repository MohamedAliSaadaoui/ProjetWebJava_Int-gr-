<?php

namespace App\Form;

use App\Entity\Participe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ParticipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_participation',DateType::class, [ // Use the correct DateType
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            //->add('nbr_place') n'est pas affiché car il est incrémenté automatiquement
            ->add('id_event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'titre', // Remplace "titre" par le bon champ de l'événement
                'disabled' => true, // Désactiver pour éviter qu'un utilisateur le modifie
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participe::class,
        ]);
    }
}
