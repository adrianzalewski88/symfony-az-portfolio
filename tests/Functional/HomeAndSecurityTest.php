<?php

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeAndSecurityTest extends WebTestCase
{
    use DatabaseTestTrait;

    public function testHomePageRendersPortfolioCategoriesAndSkills(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $category = $this->makeCategory();
        $entityManager->persist($category);
        $entityManager->persist($this->makeSkill(category: $category));
        $entityManager->flush();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Backend');
        self::assertSelectorTextContains('body', 'Symfony');
        self::assertGreaterThan(0, $crawler->count());
    }

    public function testHomePageEscapesUserSuppliedHtml(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $category = $this->makeCategory('<script>alert(1)</script>', 'html-test');
        $entityManager->persist($category);
        $entityManager->flush();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSame(0, $crawler->filterXPath('//script[contains(text(), "alert(1)")]')->count());
        self::assertSelectorTextContains('body', '<script>alert(1)</script>');
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $client = static::createClient();
        $this->resetDatabase();

        $client->request('GET', '/dashboard');

        self::assertResponseRedirects('/login');
    }

    public function testValidLoginRedirectsToDashboard(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $entityManager->persist($this->makeUser('admin@example.test', ['ROLE_ADMIN'], 'secret123'));
        $entityManager->flush();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form();
        $form['_username'] = 'admin@example.test';
        $form['_password'] = 'secret123';

        $client->submit($form);

        self::assertResponseRedirects('/dashboard');
    }

    public function testInvalidLoginShowsAuthenticationError(): void
    {
        $client = static::createClient();
        $this->resetDatabase();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form();
        $form['_username'] = 'missing@example.test';
        $form['_password'] = 'wrong-password';
        $client->submit($form);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.auth-alert--error', 'Invalid credentials');
    }

    public function testViewerCanOpenDashboardButCannotCreateContent(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $viewer = $this->makeUser('viewer@example.test', ['ROLE_USER']);
        $entityManager->persist($viewer);
        $entityManager->flush();
        $client->loginUser($viewer, 'main');

        $client->request('GET', '/dashboard/categories');
        self::assertResponseIsSuccessful();

        $client->request('GET', '/dashboard/categories/new');
        self::assertResponseStatusCodeSame(403);
    }
}
