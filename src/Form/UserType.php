<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'required' => true,
                'attr' => [
                    'placeholder' => 'e.g. demo@example.com',
                    'autocomplete' => 'email',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'e.g. Adrian',
                    'autocomplete' => 'given-name',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'e.g. Zalewski',
                    'autocomplete' => 'family-name',
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Role',
                'mapped' => false,
                'choices' => [
                    'Viewer' => 'ROLE_USER',
                    'Editor' => 'ROLE_EDITOR',
                    'Administrator' => 'ROLE_ADMIN',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'data' => $options['selected_role'],
            ])
            ->add('password', PasswordType::class, [
                'label' => $options['is_edit']
                    ? 'New Password'
                    : 'Password',
                'mapped' => false,
                'required' => !$options['is_edit'],
                'attr' => [
                    'placeholder' => $options['is_edit']
                        ? 'Leave blank to keep the current password'
                        : 'Enter a secure password',
                    'autocomplete' => 'new-password',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Account Active',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'selected_role' => 'ROLE_USER',
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
        $resolver->setAllowedTypes('selected_role', 'string');
    }
}