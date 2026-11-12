<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\LeaderboardServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DisplayLeaderboardsController extends AbstractController
{
    public function __construct(private LeaderboardServices $leaderboardServices)
    {
    }

    #[Route(
        '/leaderboards/{year}',
        name: 'leaderboards',
        requirements: ['year' => '\d+'],
        defaults: ['year' => 2025],
        methods: ['GET']
    )]
    public function displayLeaderboard(int $year): Response
    {
        $leaderboards = $this->leaderboardServices->retrieveLeaderboards($year);
        $leaderboardsData = [];
        $leaderboardsData['WA'] = [];
        foreach ($leaderboards as $leaderboardName => $leaderboard) {
            // merge the 2 WA leaderboards
            if (str_contains($leaderboardName, 'WA')) {
                $leaderboardsData['WA'] = array_merge($leaderboardsData['WA'], $this->leaderboardServices->parseJsonLeaderboard($leaderboard));
                $this->leaderboardServices->orderLeaderboard($leaderboardsData['WA']);
            } else {
                $leaderboardsData[$leaderboardName] = $this->leaderboardServices->parseJsonLeaderboard($leaderboard);
            }
        }

        return $this->render('leaderboard.twig', [
            'year' => $year,
            'leaderboardData' => $leaderboardsData,
        ]);
    }
}
