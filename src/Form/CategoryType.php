<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Category Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'e.g. Backend',
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'required' => true,
                'attr' => [
                    'placeholder' => 'e.g. backend',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Describe this skill category...',
                ],
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort Order',
                'required' => true,
                'attr' => [
                    'min' => 0,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}