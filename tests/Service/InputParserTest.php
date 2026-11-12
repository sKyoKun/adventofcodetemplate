<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\InputParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InputParserTest extends TestCase
{
    private InputParser $inputParser;

    protected function setUp(): void
    {
        $this->inputParser = new InputParser();
    }

    /**
     * @param list<string>    $lines
     * @param list<list<int>> $expectedIntegers
     */
    #[DataProvider('provideCommaSeparatedIntegers')]
    public function testItParsesCommaSeparatedIntegers(array $lines, array $expectedIntegers): void
    {
        self::assertSame($expectedIntegers, $this->inputParser->parseCommaSeparatedIntegers($lines));
    }

    /**
     * @return iterable<string, array{list<string>, list<list<int>>}>
     */
    public static function provideCommaSeparatedIntegers(): iterable
    {
        yield 'one line' => [['1,2,3'], [[1, 2, 3]]];
        yield 'negative and multi-digit numbers' => [['-1,0,42'], [[-1, 0, 42]]];
        yield 'several lines' => [['1,2', '3'], [[1, 2], [3]]];
        yield 'no line' => [[], []];
    }

    /**
     * @param list<string>       $lines
     * @param list<list<string>> $expectedCharacters
     */
    #[DataProvider('provideLinesToSplitIntoCharacters')]
    public function testItSplitsLinesIntoCharacters(array $lines, array $expectedCharacters): void
    {
        self::assertSame($expectedCharacters, $this->inputParser->splitLinesIntoCharacters($lines));
    }

    /**
     * @return iterable<string, array{list<string>, list<list<string>>}>
     */
    public static function provideLinesToSplitIntoCharacters(): iterable
    {
        yield 'spaces are characters too' => [['a bc'], [['a', ' ', 'b', 'c']]];
        yield 'several lines' => [['ab', 'c'], [['a', 'b'], ['c']]];
        yield 'blank line' => [[''], [[]]];
    }

    /**
     * @param list<string>       $lines
     * @param list<list<string>> $expectedWords
     */
    #[DataProvider('provideLinesToSplitBySpace')]
    public function testItSplitsLinesBySpace(array $lines, array $expectedWords): void
    {
        self::assertSame($expectedWords, $this->inputParser->splitLinesBySpace($lines));
    }

    /**
     * @return iterable<string, array{list<string>, list<list<string>>}>
     */
    public static function provideLinesToSplitBySpace(): iterable
    {
        yield 'single spaces' => [['a bc'], [['a', 'bc']]];
        yield 'columns aligned with several spaces' => [['3   4'], [['3', '4']]];
        yield 'leading and trailing spaces' => [['  x y  '], [['x', 'y']]];
        yield 'blank line' => [[''], [[]]];
    }

    /**
     * @param list<string>    $lines
     * @param list<list<int>> $expectedDigits
     */
    #[DataProvider('provideLinesToSplitIntoDigits')]
    public function testItSplitsLinesIntoDigits(array $lines, array $expectedDigits): void
    {
        self::assertSame($expectedDigits, $this->inputParser->splitLinesIntoDigits($lines));
    }

    /**
     * @return iterable<string, array{list<string>, list<list<int>>}>
     */
    public static function provideLinesToSplitIntoDigits(): iterable
    {
        yield 'one line' => [['1203'], [[1, 2, 0, 3]]];
        yield 'several lines' => [['12', '3'], [[1, 2], [3]]];
    }

    /**
     * @param list<string> $lines
     * @param list<string> $expectedLines
     */
    #[DataProvider('provideGridsToTranspose')]
    public function testItTransposesAGrid(array $lines, array $expectedLines): void
    {
        self::assertSame($expectedLines, $this->inputParser->transposeGrid($lines));
    }

    /**
     * @return iterable<string, array{list<string>, list<string>}>
     */
    public static function provideGridsToTranspose(): iterable
    {
        yield 'rectangular grid' => [['abc', 'def'], ['ad', 'be', 'cf']];
        yield 'single line' => [['abc'], ['a', 'b', 'c']];
        yield 'empty grid' => [[], []];
    }

    /**
     * @param list<list<string>> $grid
     * @param list<list<string>> $expectedGrid
     */
    #[DataProvider('provideGridsToRotateClockwise')]
    public function testItRotatesAGridClockwise(array $grid, array $expectedGrid): void
    {
        self::assertSame($expectedGrid, $this->inputParser->rotateGridClockwise($grid));
    }

    /**
     * @return iterable<string, array{list<list<string>>, list<list<string>>}>
     */
    public static function provideGridsToRotateClockwise(): iterable
    {
        yield 'rectangular grid' => [[['a', 'b', 'c'], ['d', 'e', 'f']], [['d', 'a'], ['e', 'b'], ['f', 'c']]];
        yield 'empty grid' => [[], []];
    }

    /**
     * @param list<list<string>> $grid
     * @param list<list<string>> $expectedGrid
     */
    #[DataProvider('provideGridsToRotateCounterClockwise')]
    public function testItRotatesAGridCounterClockwise(array $grid, array $expectedGrid): void
    {
        self::assertSame($expectedGrid, $this->inputParser->rotateGridCounterClockwise($grid));
    }

    /**
     * @return iterable<string, array{list<list<string>>, list<list<string>>}>
     */
    public static function provideGridsToRotateCounterClockwise(): iterable
    {
        yield 'rectangular grid' => [[['a', 'b', 'c'], ['d', 'e', 'f']], [['c', 'f'], ['b', 'e'], ['a', 'd']]];
        yield 'empty grid' => [[], []];
    }

    public function testRotatingClockwiseThenCounterClockwiseGivesBackTheOriginalGrid(): void
    {
        $grid = [['a', 'b', 'c'], ['d', 'e', 'f'], ['g', 'h', 'i'], ['j', 'k', 'l']];

        self::assertSame($grid, $this->inputParser->rotateGridCounterClockwise($this->inputParser->rotateGridClockwise($grid)));
    }
}
