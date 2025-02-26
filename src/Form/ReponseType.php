<?php

namespace App\Form;

use App\Entity\Reponse;
use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // The 'reponse' field, a textarea for the user's response
            ->add('reponse', TextareaType::class, [
                'label' => 'Réponse',
                'attr' => ['placeholder' => 'Entrez votre réponse ici...'],
            ])
            ->add('reclamation', TextType::class, [
                'label' => 'Id de reclamation',
                'mapped' => false, // Not mapped to the entity
                'attr' => [
                    'readonly' => true, // Prevent user from editing
                    'placeholder' => $options['id_reclamation'] ?? 'Guest', // Set default if user_name is missing
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
            'reclamation' => null, // Allow reclamations to be passed into the form
            'id_reclamation' => null,
        ]);
    }
}
