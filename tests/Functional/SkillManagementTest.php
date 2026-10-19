<?php

namespace App\Tests\Functional;

use App\Entity\Skill;
use App\Tests\Support\DatabaseTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SkillManagementTest extends WebTestCase
{
    use DatabaseTestTrait;

    public function testEditorCanCreateSkillThroughTheForm(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $editor = $this->makeUser('editor@example.test', ['ROLE_EDITOR']);
        $entityManager->persist($editor);
        $entityManager->flush();
        $client->loginUser($editor, 'main');

        $crawler = $client->request('GET', '/dashboard/skills/new');
        $form = $crawler->selectButton('Create Skill')->form();
        $form['skill[name]'] = 'Symfony';
        $form['skill[slug]'] = 'symfony';
        $form['skill[description]'] = 'PHP framework';
        $form['skill[rating]'] = '95';
        $form['skill[sortOrder]'] = '1';
        $form['skill[category]'] = '';
        $client->submit($form);

        self::assertResponseRedirects('/dashboard/skills');
        self::assertSame(1, static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Skill::class)
            ->count([]));
    }

    public function testDashboardSearchAndSortingParametersChangeTheResults(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $viewer = $this->makeUser('viewer@example.test', ['ROLE_USER']);
        $entityManager->persist($viewer);
        $entityManager->persist($this->makeSkill('Symfony', 'symfony', 95, 0, null, 'PHP framework'));
        $entityManager->persist($this->makeSkill('React', 'react', 75, 1, null, 'UI library'));
        $entityManager->flush();
        $client->loginUser($viewer, 'main');

        $client->request('GET', '/dashboard/skills', [
            'q' => 'php',
            'sort' => 'rating',
            'direction' => 'desc',
            'view' => 'table',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Symfony');
        self::assertSelectorTextNotContains('body', 'React');
    }

    public function testUnsupportedDashboardFiltersFallBackWithoutAnError(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $viewer = $this->makeUser('viewer@example.test', ['ROLE_USER']);
        $entityManager->persist($viewer);
        $entityManager->flush();
        $client->loginUser($viewer, 'main');

        $client->request('GET', '/dashboard/skills?sort=arbitrary&direction=sideways&view=unknown');

        self::assertResponseIsSuccessful();
    }

    public function testViewerCannotOpenSkillCreationForm(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $viewer = $this->makeUser('viewer@example.test', ['ROLE_USER']);
        $entityManager->persist($viewer);
        $entityManager->flush();
        $client->loginUser($viewer, 'main');

        $client->request('GET', '/dashboard/skills/new');

        self::assertResponseStatusCodeSame(403);
    }

    public function testEditorCanEditSkillAndUpdateTimestamp(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $editor = $this->makeUser('editor@example.test', ['ROLE_EDITOR']);
        $skill = $this->makeSkill();
        $skill->setUpdatedAt(new \DateTimeImmutable('2000-01-01 00:00:00 UTC'));
        $entityManager->persist($editor);
        $entityManager->persist($skill);
        $entityManager->flush();
        $skillId = $skill->getId();
        $client->loginUser($editor, 'main');

        $crawler = $client->request('GET', '/dashboard/skills/'.$skillId.'/edit');
        $form = $crawler->selectButton('Save Changes')->form();
        $form['skill[name]'] = 'Symfony Framework';
        $form['skill[slug]'] = 'symfony-framework';
        $form['skill[description]'] = 'PHP framework';
        $form['skill[rating]'] = '95';
        $form['skill[sortOrder]'] = '1';
        $form['skill[category]'] = '';
        $client->submit($form);

        self::assertResponseRedirects('/dashboard/skills');
        $updated = static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Skill::class)
            ->find($skillId);
        self::assertSame('Symfony Framework', $updated?->getName());
        self::assertGreaterThan(new \DateTimeImmutable('2000-01-01 00:00:00 UTC'), $updated?->getUpdatedAt());
    }

    public function testMissingSkillReturnsNotFound(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $editor = $this->makeUser('editor@example.test', ['ROLE_EDITOR']);
        $entityManager->persist($editor);
        $entityManager->flush();
        $client->loginUser($editor, 'main');

        $client->request('GET', '/dashboard/skills/999999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testGetRequestCannotDeleteSkill(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $admin = $this->makeUser('admin@example.test', ['ROLE_ADMIN']);
        $skill = $this->makeSkill();
        $entityManager->persist($admin);
        $entityManager->persist($skill);
        $entityManager->flush();
        $client->loginUser($admin, 'main');

        $client->request('GET', '/dashboard/skills/'.$skill->getId().'/delete');

        self::assertResponseStatusCodeSame(405);
    }

    public function testInvalidCsrfTokenDoesNotDeleteSkill(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $admin = $this->makeUser('admin@example.test', ['ROLE_ADMIN']);
        $skill = $this->makeSkill();
        $entityManager->persist($admin);
        $entityManager->persist($skill);
        $entityManager->flush();
        $client->loginUser($admin, 'main');

        $client->request('POST', '/dashboard/skills/'.$skill->getId().'/delete', ['_token' => 'invalid']);

        self::assertResponseStatusCodeSame(403);
        self::assertNotNull(static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Skill::class)
            ->find($skill->getId()));
    }

    public function testValidCsrfTokenAllowsAdminToDeleteSkill(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $admin = $this->makeUser('admin@example.test', ['ROLE_ADMIN']);
        $skill = $this->makeSkill();
        $entityManager->persist($admin);
        $entityManager->persist($skill);
        $entityManager->flush();
        $skillId = $skill->getId();
        $client->loginUser($admin, 'main');

        $crawler = $client->request('GET', '/dashboard/skills');

        $token = $crawler->filter(sprintf(
            'form[action="/dashboard/skills/%d/delete"] input[name="_token"]',
            $skillId
        ))->attr('value');

        self::assertNotEmpty($token, 'The skill delete form should contain a CSRF token.');

        $client->request('POST', '/dashboard/skills/'.$skillId.'/delete', ['_token' => $token]);

        self::assertResponseRedirects('/dashboard/skills');
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        self::assertNull($entityManager->getRepository(Skill::class)->find($skillId));
    }
}
