<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Skill;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Skill Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'e.g. Symfony',
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'required' => true,
                'attr' => [
                    'placeholder' => 'e.g. symfony',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Describe your experience with this skill...',
                ],
            ])
            ->add('rating', IntegerType::class, [
                'label' => 'Rating',
                'required' => true,
                'help' => 'Enter a value from 0 to 100.',
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort Order',
                'required' => true,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a category',
                'required' => false,
                'label' => 'Category',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skill::class,
        ]);
    }
}