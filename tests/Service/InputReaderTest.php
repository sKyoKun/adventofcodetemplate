<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\InputReader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

final class InputReaderTest extends KernelTestCase
{
    private Filesystem $filesystem;

    private string $temporaryInputDirectory;

    private InputReader $inputReader;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->temporaryInputDirectory = $this->filesystem->tempnam(sys_get_temp_dir(), 'aoc-inputs-');
        // tempnam() creates a file: replace it by a directory
        $this->filesystem->remove($this->temporaryInputDirectory);
        $this->filesystem->mkdir($this->temporaryInputDirectory);

        $this->inputReader = new InputReader($this->temporaryInputDirectory);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->temporaryInputDirectory);

        parent::tearDown();
    }

    public function testItReadsFilesFromTheInputsDirectoryOfTheProject(): void
    {
        $inputReader = self::getContainer()->get(InputReader::class);
        $projectInputDirectory = self::getContainer()->getParameter('kernel.project_dir').'/inputs';

        self::assertSame(
            file($projectInputDirectory.'/day1test.txt', \FILE_IGNORE_NEW_LINES),
            $inputReader->getInput('day1test.txt'),
        );
    }

    public function testItReturnsOneTrimmedEntryPerLineAndKeepsBlankLines(): void
    {
        $this->filesystem->dumpFile($this->temporaryInputDirectory.'/input.txt', "  a b  \n\nc\n");

        self::assertSame(['a b', '', 'c'], $this->inputReader->getInput('input.txt'));
    }

    public function testItKeepsSpacesAroundLinesWhenTrimmingIsDisabled(): void
    {
        $this->filesystem->dumpFile($this->temporaryInputDirectory.'/input.txt', "    [D]    \n[N] [C]\n");

        self::assertSame(['    [D]    ', '[N] [C]'], $this->inputReader->getInput('input.txt', trimLines: false));
    }

    public function testItRemovesWindowsLineEndingsWhenTrimmingIsDisabled(): void
    {
        $this->filesystem->dumpFile($this->temporaryInputDirectory.'/input.txt', "a \r\nb\r\n");

        self::assertSame(['a ', 'b'], $this->inputReader->getInput('input.txt', trimLines: false));
    }

    public function testItReturnsNoLineForAnEmptyFile(): void
    {
        $this->filesystem->dumpFile($this->temporaryInputDirectory.'/input.txt', '');

        self::assertSame([], $this->inputReader->getInput('input.txt'));
    }

    public function testItThrowsAClearErrorWhenTheInputFileDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The input file "day42.txt" does not exist');

        $this->inputReader->getInput('day42.txt');
    }
}
