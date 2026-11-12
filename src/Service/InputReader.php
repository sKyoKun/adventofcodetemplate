<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class InputReader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/inputs/')]
        private readonly string $inputDirectory,
    ) {
    }

    public function getInput(string $file): array
    {
        $inputs = [];
        $content = fopen($this->inputDirectory.$file, 'r');

        while (($line = fgets($content)) !== false) {
            $lineWithoutSpaces = trim($line);
            $inputs[] = $lineWithoutSpaces;
        }

        fclose($content);

        return $inputs;
    }
}
