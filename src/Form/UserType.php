<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\LessThan;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom d\'utilisateur ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 20,
                        'minMessage' => 'Le nom d\'utilisateur doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom d\'utilisateur ne peut pas comporter plus de {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'email ne peut pas être vide.',
                    ]),
                    new Assert\Email([
                        'message' => 'Veuillez entrer un email valide.',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                ],
            ])
            ->add('num_tel', TelType::class, [
                'label' => 'Numéro de téléphone',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le numéro de téléphone ne peut pas être vide.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\+?\d{10,15}$/',
                        'message' => 'Le numéro de téléphone doit être valide (ex: +1234567890).',
                    ]),
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'adresse ne peut pas être vide.',
                    ]),
                ],
            ])
            ->add('date_naiss', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'constraints' => [
                    new LessThan([
                        'value' => 'today',
                        'message' => 'La date de naissance doit être dans le passé.',
                    ]),
                ],
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '500M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG)',
                    ])
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
