<?php

namespace App\Controller;

use App\Entity\Skill;
use App\Form\SkillType;
use App\Repository\CategoryRepository;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use SortDirection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/skills')]
class SkillController extends AbstractController
{
    #[Route('', name: 'dashboard_skills_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        SkillRepository $skillRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $search = trim((string) $request->query->get('q', ''));

        $categorySlug = trim(
            (string) $request->query->get('category', '')
        );

        $sort = (string) $request->query->get(
            'sort',
            'sortOrder'
        );

        $allowedSorts = [
            'sortOrder',
            'name',
            'rating',
            'updatedAt',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'sortOrder';
        }

        $directionValue = strtolower(
            (string) $request->query->get(
                'direction',
                'asc'
            )
        );

        if (!in_array($directionValue, ['asc', 'desc'], true)) {
            $directionValue = 'asc';
        }

        $view = strtolower(
            (string) $request->query->get(
                'view',
                'cards'
            )
        );

        if (!in_array($view, ['cards', 'table'], true)) {
            $view = 'cards';
        }

        $category = null;

        if ($categorySlug !== '') {
            $category = $categoryRepository->findOneBySlug(
                $categorySlug
            );
        }

        $direction = $directionValue === 'desc'
            ? SortDirection::Descending
            : SortDirection::Ascending;

        $skills = $skillRepository->findForDashboard(
            $search !== '' ? $search : null,
            $category,
            $sort,
            $direction
        );

        /*
         * Dashboard statistics
         */
        $totalSkills = count($skills);

        $categoryIds = [];
        $totalRating = 0;

        foreach ($skills as $skill) {
            $totalRating += $skill->getRating();

            if ($skill->getCategory() !== null) {
                $categoryIds[
                    $skill->getCategory()->getId()
                ] = true;
            }
        }

        $categoryCount = count($categoryIds);

        $averageRating = $totalSkills > 0
            ? (int) round(
                $totalRating / $totalSkills
            )
            : 0;

        return $this->render(
            'dashboard/skills/index.html.twig',
            [
                'skills' => $skills,

                'categories' => $categoryRepository
                    ->findAllOrdered(),

                'search' => $search,

                'selectedCategory' => $categorySlug,

                'selectedSort' => $sort,

                'selectedDirection' => $directionValue,

                'view' => $view,

                'totalSkills' => $totalSkills,

                'categoryCount' => $categoryCount,

                'averageRating' => $averageRating,
            ]
        );
    }

    #[Route(
        '/new',
        name: 'dashboard_skills_new',
        methods: ['GET', 'POST']
    )]
    #[IsGranted('ROLE_EDITOR')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $skill = new Skill();

        $form = $this->createForm(
            SkillType::class,
            $skill
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $entityManager->persist($skill);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Skill created successfully.'
            );

            return $this->redirectToRoute(
                'dashboard_skills_index'
            );
        }

        return $this->render(
            'dashboard/skills/new.html.twig',
            [
                'skill' => $skill,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/edit',
        name: 'dashboard_skills_edit',
        methods: ['GET', 'POST']
    )]
    #[IsGranted('ROLE_EDITOR')]
    public function edit(
        Skill $skill,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(
            SkillType::class,
            $skill
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $skill->setUpdatedAt(
                new \DateTimeImmutable()
            );

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Skill updated successfully.'
            );

            return $this->redirectToRoute(
                'dashboard_skills_index'
            );
        }

        return $this->render(
            'dashboard/skills/edit.html.twig',
            [
                'skill' => $skill,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/delete',
        name: 'dashboard_skills_delete',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Skill $skill,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_skill_' . $skill->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $entityManager->remove($skill);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Skill deleted successfully.'
        );

        return $this->redirectToRoute(
            'dashboard_skills_index'
        );
    }
}