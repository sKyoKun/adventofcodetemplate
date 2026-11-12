<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\GenerateNewDayCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application as FrameworkApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

final class GenerateNewDayCommandTest extends KernelTestCase
{
    private const array EXPECTED_DAY_3_FILES = [
        'src/Controller/Day3Controller.php',
        'src/Service/Day3Service.php',
        'tests/Service/Day3ServiceTest.php',
        'features/day3.feature',
        'inputs/day3.txt',
        'inputs/day3test.txt',
    ];

    private Filesystem $filesystem;

    private string $temporaryProjectDirectory;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->temporaryProjectDirectory = $this->filesystem->tempnam(sys_get_temp_dir(), 'aoc-generate-day-');
        // tempnam() creates a file: replace it by a directory
        $this->filesystem->remove($this->temporaryProjectDirectory);
        $this->filesystem->mkdir($this->temporaryProjectDirectory);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->temporaryProjectDirectory);

        parent::tearDown();
    }

    public function testItIsRegisteredInTheApplication(): void
    {
        $application = new FrameworkApplication(self::bootKernel());

        $command = $application->find('generate:day');

        self::assertSame('generate:day', $command->getName());
        self::assertTrue($command->getDefinition()->getArgument('day')->isRequired());
    }

    public function testItGeneratesAllFilesOfTheDay(): void
    {
        $commandTester = $this->createCommandTester();

        $exitCode = $commandTester->execute(['day' => '3']);

        self::assertSame(Command::SUCCESS, $exitCode);
        foreach (self::EXPECTED_DAY_3_FILES as $expectedFile) {
            self::assertFileExists($this->temporaryProjectDirectory.'/'.$expectedFile);
            self::assertStringContainsString($expectedFile, $commandTester->getDisplay());
        }
    }

    public function testItReplacesTheDayPlaceholderInGeneratedFiles(): void
    {
        $this->createCommandTester()->execute(['day' => '3']);

        $controllerContent = file_get_contents($this->temporaryProjectDirectory.'/src/Controller/Day3Controller.php');
        self::assertStringContainsString('class Day3Controller', $controllerContent);
        self::assertStringContainsString("#[Route('/day3'", $controllerContent);

        foreach (self::EXPECTED_DAY_3_FILES as $expectedFile) {
            self::assertStringNotContainsString(
                '[calendarDay]',
                file_get_contents($this->temporaryProjectDirectory.'/'.$expectedFile),
                \sprintf('The placeholder has not been replaced in "%s".', $expectedFile),
            );
        }
    }

    public function testItDoesNotOverwriteExistingFiles(): void
    {
        $this->filesystem->dumpFile($this->temporaryProjectDirectory.'/src/Service/Day3Service.php', 'my solution');
        $this->filesystem->dumpFile($this->temporaryProjectDirectory.'/inputs/day3.txt', 'my puzzle input');
        $commandTester = $this->createCommandTester();

        $exitCode = $commandTester->execute(['day' => '3']);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringEqualsFile($this->temporaryProjectDirectory.'/src/Service/Day3Service.php', 'my solution');
        self::assertStringEqualsFile($this->temporaryProjectDirectory.'/inputs/day3.txt', 'my puzzle input');
        self::assertStringContainsString('Already existing files', $commandTester->getDisplay());
        self::assertFileExists($this->temporaryProjectDirectory.'/src/Controller/Day3Controller.php');
    }

    #[DataProvider('provideOutOfRangeDays')]
    public function testItRejectsADayOutsideTheAdventCalendar(string $day): void
    {
        $commandTester = $this->createCommandTester();

        $exitCode = $commandTester->execute(['day' => $day]);

        self::assertSame(Command::INVALID, $exitCode);
        self::assertStringContainsString('The day must be between 1 and 25', $commandTester->getDisplay());
        self::assertDirectoryDoesNotExist($this->temporaryProjectDirectory.'/src');
        self::assertDirectoryDoesNotExist($this->temporaryProjectDirectory.'/inputs');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideOutOfRangeDays(): iterable
    {
        yield 'day zero' => ['0'];
        yield 'day after Christmas' => ['26'];
    }

    public function testItRejectsANonNumericDay(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createCommandTester()->execute(['day' => 'three']);
    }

    private function createCommandTester(): CommandTester
    {
        $command = new GenerateNewDayCommand(
            $this->filesystem,
            \dirname(__DIR__, 2).'/skeleton',
            $this->temporaryProjectDirectory,
        );

        $application = new Application();
        $application->addCommand($command);

        return new CommandTester($application->find('generate:day'));
    }
}
