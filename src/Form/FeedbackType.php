<?php

namespace App\Form;

use App\Entity\Feedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('feedback_text', TextareaType::class, [
                'label' => 'Donner votre reponse',
                'attr' => [
                    'placeholder' => 'Entrez votre reponse ici...',
                ],
            ])
            ->add('userName', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'placeholder' => 'Votre nom',
                ],
            ])
            ->add('approved', ChoiceType::class, [
                'label' => 'Statut d\'approbation',
                'choices' => [
                    'Non approuvé' => 0,
                    'Approuvé' => 1,
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}