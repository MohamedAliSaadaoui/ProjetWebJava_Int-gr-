<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class ArticleType extends AbstractType
{
    
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('title', TextType::class, [
            'label' => 'Titre',
            'required' => true,
            'attr' => ['maxlength' => 60],
        ])
        ->add('date', DateType::class, [
            'label' => 'Date',
            'widget' => 'single_text',
            'data' => new \DateTime(),
            'required' => true,
        ])
        ->add('image', FileType::class, [
            'label' => 'Image (fichier)',
            'mapped' => false, // Empêche Symfony de l'associer directement à l'entité
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                    'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WebP)',
                ])
            ],
        ])
        ->add('content', TextareaType::class, [
            'label' => 'Contenu de l\'article',
            'attr' => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Écrivez votre article ici...'],
        ]);
}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
