<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AdventCalendar;
use App\Service\Leaderboard\LeaderboardBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DisplayLeaderboardsController extends AbstractController
{
    public function __construct(
        private readonly LeaderboardBuilder $leaderboardBuilder,
        private readonly AdventCalendar $adventCalendar,
        #[Autowire(env: 'int:AOC_YEAR')]
        private readonly int $defaultYear,
    ) {
    }

    #[Route('/leaderboards/{year}', name: 'leaderboards', requirements: ['year' => '\d{4}'], methods: ['GET'])]
    public function displayLeaderboard(?int $year = null): Response
    {
        $year ??= $this->defaultYear;

        return $this->render('leaderboard/index.html.twig', [
            'year' => $year,
            'numberOfDays' => $this->adventCalendar->countDays($year),
            'leaderboardData' => $this->leaderboardBuilder->buildLeaderboards($year),
        ]);
    }
}
