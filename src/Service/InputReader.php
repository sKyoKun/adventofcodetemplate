<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class InputReader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/inputs')]
        private string $inputDirectory,
    ) {
    }

    /**
     * Returns one entry per line of the file, blank lines included.
     *
     * @param bool $trimLines true to remove the spaces around each line, false to only remove the line break
     *                        (useful when leading spaces matter, e.g. drawings or aligned columns)
     *
     * @return list<string>
     */
    public function getInput(string $file, bool $trimLines = true): array
    {
        $path = $this->inputDirectory.'/'.$file;

        if (false === is_file($path)) {
            throw new \RuntimeException(\sprintf('The input file "%s" does not exist, create it in "%s".', $file, $this->inputDirectory));
        }

        $lines = file($path, \FILE_IGNORE_NEW_LINES);

        if (false === $lines) {
            throw new \RuntimeException(\sprintf('The input file "%s" cannot be read.', $path));
        }

        $inputs = [];

        foreach ($lines as $line) {
            if (true === $trimLines) {
                $inputs[] = trim($line);
            } else {
                // FILE_IGNORE_NEW_LINES only removes "\n": the "\r" of Windows line endings must be removed too
                $inputs[] = rtrim($line, "\r");
            }
        }

        return $inputs;
    }
}
