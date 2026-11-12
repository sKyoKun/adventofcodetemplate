<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'generate:day',
    description: 'Generates the controller, service, tests and input files of an Advent of Code day',
)]
final readonly class GenerateNewDayCommand
{
    private const string DAY_PLACEHOLDER = '[calendarDay]';

    private const int FIRST_DAY = 1;

    private const int LAST_DAY = 25;

    /**
     * Skeleton file name => generated file path, relative to the project directory.
     */
    private const array SKELETON_TARGETS = [
        'Controller.php.txt' => 'src/Controller/Day[calendarDay]Controller.php',
        'Service.php.txt' => 'src/Service/Day[calendarDay]Service.php',
        'ServiceTest.php.txt' => 'tests/Service/Day[calendarDay]ServiceTest.php',
        'day.feature.txt' => 'features/day[calendarDay].feature',
    ];

    /**
     * Empty input files to create, relative to the project directory.
     */
    private const array INPUT_FILES = [
        'inputs/day[calendarDay].txt',
        'inputs/day[calendarDay]test.txt',
    ];

    public function __construct(
        private Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%/skeleton')]
        private string $skeletonDirectory,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDirectory,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'Advent of Code day number (1 to 25)')]
        int $day,
    ): int {
        if (self::FIRST_DAY > $day || self::LAST_DAY < $day) {
            $io->error(\sprintf('The day must be between %d and %d, got %d.', self::FIRST_DAY, self::LAST_DAY, $day));

            return Command::INVALID;
        }

        $createdFiles = [];
        $skippedFiles = [];

        foreach (self::SKELETON_TARGETS as $skeletonFileName => $targetPathPattern) {
            $targetPath = $this->replaceDayPlaceholder($targetPathPattern, $day);
            $skeletonContent = $this->filesystem->readFile($this->skeletonDirectory.'/'.$skeletonFileName);

            if (true === $this->createFileIfMissing($targetPath, $this->replaceDayPlaceholder($skeletonContent, $day))) {
                $createdFiles[] = $targetPath;
            } else {
                $skippedFiles[] = $targetPath;
            }
        }

        foreach (self::INPUT_FILES as $inputPathPattern) {
            $inputPath = $this->replaceDayPlaceholder($inputPathPattern, $day);

            if (true === $this->createFileIfMissing($inputPath, '')) {
                $createdFiles[] = $inputPath;
            } else {
                $skippedFiles[] = $inputPath;
            }
        }

        if (0 < \count($createdFiles)) {
            $io->section('Created files');
            $io->listing($createdFiles);
        }

        if (0 < \count($skippedFiles)) {
            $io->section('Already existing files (left untouched)');
            $io->listing($skippedFiles);
        }

        $io->success(\sprintf('Day %d is ready.', $day));

        return Command::SUCCESS;
    }

    private function replaceDayPlaceholder(string $content, int $day): string
    {
        return str_replace(self::DAY_PLACEHOLDER, (string) $day, $content);
    }

    /**
     * @return bool true if the file has been created, false if it already existed
     */
    private function createFileIfMissing(string $relativePath, string $content): bool
    {
        $absolutePath = $this->projectDirectory.'/'.$relativePath;

        if (true === $this->filesystem->exists($absolutePath)) {
            return false;
        }

        $this->filesystem->dumpFile($absolutePath, $content);

        return true;
    }
}
