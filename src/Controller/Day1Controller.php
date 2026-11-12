<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Day1Service;
use App\Service\InputReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/day1', name: 'day1')]
class Day1Controller extends AbstractController
{
    private InputReader $inputReader;

    private Day1Service $day1Service;

    public function __construct(InputReader $inputReader, Day1Service $day1Service)
    {
        $this->inputReader = $inputReader;
        $this->day1Service = $day1Service;
    }

    #[Route('/1/{file}', name: 'day1_1', defaults: ['file' => 'day1'])]
    public function part1(string $file): JsonResponse
    {
        return new JsonResponse('', Response::HTTP_OK);
    }

    #[Route('/2/{file}', name: 'day1_2', defaults: ['file' => 'day1'])]
    public function part2(string $file): JsonResponse
    {
        return new JsonResponse('', Response::HTTP_OK);
    }
}
