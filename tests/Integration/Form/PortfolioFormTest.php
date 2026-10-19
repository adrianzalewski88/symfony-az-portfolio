<?php

namespace App\Tests\Integration\Form;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
use App\Form\SkillType;
use App\Form\UserType;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

final class PortfolioFormTest extends KernelTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->resetDatabase();
    }

    public function testCategoryFormMapsSubmittedFieldsToEntity(): void
    {
        $category = new Category();
        $form = static::getContainer()
            ->get(FormFactoryInterface::class)
            ->create(CategoryType::class, $category, ['csrf_protection' => false]);

        $form->submit([
            'name' => 'Backend',
            'slug' => 'backend',
            'description' => 'Server-side skills',
            'sortOrder' => '2',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('Backend', $category->getName());
        self::assertSame('backend', $category->getSlug());
        self::assertSame(2, $category->getSortOrder());
    }

    public function testSkillFormMapsCategoryChoiceToEntity(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $category = $this->makeCategory();
        $entityManager->persist($category);
        $entityManager->flush();

        $form = static::getContainer()
            ->get(FormFactoryInterface::class)
            ->create(SkillType::class, null, ['csrf_protection' => false]);

        $form->submit([
            'name' => 'Symfony',
            'slug' => 'symfony',
            'description' => '',
            'rating' => '95',
            'sortOrder' => '1',
            'category' => (string) $category->getId(),
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame($category->getId(), $form->getData()->getCategory()?->getId());
        self::assertSame(95, $form->getData()->getRating());
    }

    public function testUserCreationAndEditFormsHaveDifferentPasswordRequirements(): void
    {
        $formFactory = static::getContainer()->get(FormFactoryInterface::class);
        $createForm = $formFactory->create(UserType::class, new User(), [
            'csrf_protection' => false,
            'is_edit' => false,
        ]);
        $editForm = $formFactory->create(UserType::class, new User(), [
            'csrf_protection' => false,
            'is_edit' => true,
        ]);

        self::assertTrue($createForm->get('password')->getConfig()->getRequired());
        self::assertFalse($editForm->get('password')->getConfig()->getRequired());
        self::assertTrue($createForm->has('role'));
        self::assertTrue($createForm->has('isActive'));
    }

    public function testUserFormRejectsRoleOutsideConfiguredChoices(): void
    {
        $form = static::getContainer()
            ->get(FormFactoryInterface::class)
            ->create(UserType::class, new User(), ['csrf_protection' => false]);

        $form->submit([
            'email' => 'viewer@example.test',
            'firstName' => 'Test',
            'lastName' => 'Viewer',
            'role' => 'ROLE_SUPERUSER',
            'password' => 'secret',
            'isActive' => '1',
        ]);

        self::assertFalse($form->isValid());
    }

    public function testSkillFormRejectsRatingAboveMaximum(): void
    {
        $form = static::getContainer()
            ->get(FormFactoryInterface::class)
            ->create(SkillType::class, null, ['csrf_protection' => false]);

        $form->submit([
            'name' => 'Symfony',
            'slug' => 'symfony',
            'description' => '',
            'rating' => '101',
            'sortOrder' => '0',
            'category' => '',
        ]);

        self::assertFalse($form->isValid());
    }
}
