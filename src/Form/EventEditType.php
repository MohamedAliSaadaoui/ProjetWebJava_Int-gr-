<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('dateDebut')
            ->add('dateFin')
            ->add('lieu')
            ->add('statut')
            ->add('categorie')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
