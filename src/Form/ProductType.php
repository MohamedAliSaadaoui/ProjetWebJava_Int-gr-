<?php
namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('objetAVendre', TextType::class, [
                'label' => 'Product Name',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a product name',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'The product name must be at least {{ limit }} characters long',
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a description',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Description should be at least {{ limit }} characters long',
                    ]),
                ],
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Product Photo',
                'mapped' => false, // This field is not directly mapped to the entity
                'required' => false, // Make it optional for editing existing products
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WEBP)',
                    ])
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Category/Genre',
                'choices' => [
                    'Men' => 'Homme',
                    'Women' => 'Femme',
                    'Kids' => 'Enfant',
                    'Unisex' => 'Unisexe',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a category',
                    ]),
                ],
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'Condition',
                'choices' => [
                    'New' => 'Neuf',
                    'Used' => 'Utilisé',
                    'Refurbished' => 'Reconditionné',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a product condition',
                    ]),
                ],
            ])
            ->add('taille', ChoiceType::class, [
                'label' => 'Size',
                'choices' => [
                    'XS' => 'XS',
                    'S' => 'S',
                    'M' => 'M',
                    'L' => 'L',
                    'XL' => 'XL',
                    'XXL' => 'XXL',
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a size',
                    ]),
                ],
            ])
            ->add('couleur', TextType::class, [
                'label' => 'Color',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a color',
                    ]),
                ],
            ])
            ->add('prixDeVente', NumberType::class, [
                'label' => 'Selling Price',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the selling price',
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'The price must be a number',
                    ]),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'The price must be greater than zero',
                    ]),
                ],
                'scale' => 2,
            ])
            ->add('prixOriginal', NumberType::class, [
                'label' => 'Original Price',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the original price',
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'The price must be a number',
                    ]),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'The price must be greater than zero',
                    ]),
                ],
                'scale' => 2,
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Phone Number',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a phone number',
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]{10}$/',
                        'message' => 'Please enter a valid 10-digit phone number',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Address',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an address',
                    ]),
                ],
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Postal Code',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a postal code',
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]{5}$/',
                        'message' => 'Please enter a valid 5-digit postal code',
                    ]),
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'City',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a city',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
