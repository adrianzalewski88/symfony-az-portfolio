<?php

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Entity\Skill;
use App\Tests\Support\DatabaseTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CategoryManagementTest extends WebTestCase
{
    use DatabaseTestTrait;

    public function testEditorCanCreateCategoryThroughTheForm(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $editor = $this->makeUser('editor@example.test', ['ROLE_EDITOR']);
        $entityManager->persist($editor);
        $entityManager->flush();
        $client->loginUser($editor, 'main');

        $crawler = $client->request('GET', '/dashboard/categories/new');
        $form = $crawler->selectButton('Create Category')->form();
        $form['category[name]'] = 'Backend';
        $form['category[slug]'] = 'backend';
        $form['category[description]'] = 'Server-side skills';
        $form['category[sortOrder]'] = '1';
        $client->submit($form);

        self::assertResponseRedirects('/dashboard/categories');
        self::assertSame(1, static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Category::class)
            ->count([]));
    }

    public function testDeletingCategoryLeavesItsSkillsUncategorized(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $admin = $this->makeUser('admin@example.test', ['ROLE_ADMIN']);
        $category = $this->makeCategory();
        $skill = $this->makeSkill(category: $category);
        $entityManager->persist($admin);
        $entityManager->persist($category);
        $entityManager->persist($skill);
        $entityManager->flush();
        $categoryId = $category->getId();
        $skillId = $skill->getId();
        $client->loginUser($admin, 'main');
        $client->loginUser($admin, 'main');

        $crawler = $client->request('GET', '/dashboard/categories');

        $token = $crawler->filter(sprintf(
            'form[action="/dashboard/categories/%d/delete"] input[name="_token"]',
            $categoryId
        ))->attr('value');

        self::assertNotEmpty($token, 'The category delete form should contain a CSRF token.');

        $client->request('POST', '/dashboard/categories/'.$categoryId.'/delete', ['_token' => $token]);

        self::assertResponseRedirects('/dashboard/categories');
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        self::assertNull($entityManager->getRepository(Category::class)->find($categoryId));
        self::assertNull($entityManager->getRepository(Skill::class)->find($skillId)?->getCategory());
    }

    public function testMissingCategoryReturnsNotFound(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $editor = $this->makeUser('editor@example.test', ['ROLE_EDITOR']);
        $entityManager->persist($editor);
        $entityManager->flush();
        $client->loginUser($editor, 'main');

        $client->request('GET', '/dashboard/categories/999999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditorCanEditCategoryAndUpdateTimestamp(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $editor = $this->makeUser('editor@example.test', ['ROLE_EDITOR']);
        $category = $this->makeCategory();
        $category->setUpdatedAt(new \DateTimeImmutable('2000-01-01 00:00:00 UTC'));
        $entityManager->persist($editor);
        $entityManager->persist($category);
        $entityManager->flush();
        $categoryId = $category->getId();
        $client->loginUser($editor, 'main');

        $crawler = $client->request('GET', '/dashboard/categories/'.$categoryId.'/edit');
        $form = $crawler->selectButton('Save Changes')->form();
        $form['category[name]'] = 'Backend Engineering';
        $form['category[slug]'] = 'backend-engineering';
        $form['category[description]'] = 'Server-side skills';
        $form['category[sortOrder]'] = '2';
        $client->submit($form);

        self::assertResponseRedirects('/dashboard/categories');
        $updated = static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Category::class)
            ->find($categoryId);
        self::assertSame('Backend Engineering', $updated?->getName());
        self::assertGreaterThan(new \DateTimeImmutable('2000-01-01 00:00:00 UTC'), $updated?->getUpdatedAt());
    }

    public function testGetRequestCannotDeleteCategory(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $admin = $this->makeUser('admin@example.test', ['ROLE_ADMIN']);
        $category = $this->makeCategory();
        $entityManager->persist($admin);
        $entityManager->persist($category);
        $entityManager->flush();
        $client->loginUser($admin, 'main');

        $client->request('GET', '/dashboard/categories/'.$category->getId().'/delete');

        self::assertResponseStatusCodeSame(405);
    }
}
