<?php
// src/Form/NewPasswordType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'invalid_message' => 'Les mots de passe ne correspondent pas',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
        ]);
    }
}