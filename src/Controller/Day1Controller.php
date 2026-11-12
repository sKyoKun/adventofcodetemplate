<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Day1Service;
use App\Service\InputReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/day1', name: 'day1_', methods: ['GET'])]
final class Day1Controller extends AbstractController
{
    public function __construct(
        private readonly InputReader $inputReader,
        private readonly Day1Service $day1Service,
    ) {
    }

    #[Route('/1/{file}', name: 'part1', requirements: ['file' => '\w+'], defaults: ['file' => 'day1'])]
    public function part1(string $file): JsonResponse
    {
        $lines = $this->inputReader->getInput($file.'.txt');

        return new JsonResponse('');
    }

    #[Route('/2/{file}', name: 'part2', requirements: ['file' => '\w+'], defaults: ['file' => 'day1'])]
    public function part2(string $file): JsonResponse
    {
        $lines = $this->inputReader->getInput($file.'.txt');

        return new JsonResponse('');
    }
}
