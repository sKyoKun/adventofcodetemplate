<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Helpers to turn the lines returned by InputReader into the structures puzzles usually need.
 */
final class InputParser
{
    /**
     * "1,-2,3" becomes [1, -2, 3].
     *
     * @param list<string> $lines
     *
     * @return list<list<int>>
     */
    public function parseCommaSeparatedIntegers(array $lines): array
    {
        $integerLines = [];

        foreach ($lines as $line) {
            $integers = [];
            foreach (explode(',', $line) as $number) {
                $integers[] = (int) $number;
            }
            $integerLines[] = $integers;
        }

        return $integerLines;
    }

    /**
     * "a bc" becomes ['a', ' ', 'b', 'c'].
     *
     * @param list<string> $lines
     *
     * @return list<list<string>>
     */
    public function splitLinesIntoCharacters(array $lines): array
    {
        $characterLines = [];

        foreach ($lines as $line) {
            $characterLines[] = str_split($line);
        }

        return $characterLines;
    }

    /**
     * "a   bc" becomes ['a', 'bc']: consecutive spaces count as one separator, leading and trailing ones are ignored.
     *
     * @param list<string> $lines
     *
     * @return list<list<string>>
     */
    public function splitLinesBySpace(array $lines): array
    {
        $wordLines = [];

        foreach ($lines as $line) {
            // "\s+" splits on any number of spaces, PREG_SPLIT_NO_EMPTY drops the empty words around the line
            $wordLines[] = preg_split('/\s+/', $line, -1, \PREG_SPLIT_NO_EMPTY);
        }

        return $wordLines;
    }

    /**
     * "1203" becomes [1, 2, 0, 3].
     *
     * @param list<string> $lines
     *
     * @return list<list<int>>
     */
    public function splitLinesIntoDigits(array $lines): array
    {
        $digitLines = [];

        foreach ($lines as $line) {
            $digits = [];
            foreach (str_split($line) as $character) {
                $digits[] = (int) $character;
            }
            $digitLines[] = $digits;
        }

        return $digitLines;
    }

    /**
     * Rows become columns: ['abc', 'def'] becomes ['ad', 'be', 'cf']. The grid must be rectangular.
     *
     * @param list<string> $lines
     *
     * @return list<string>
     */
    public function transposeGrid(array $lines): array
    {
        if ([] === $lines) {
            return [];
        }

        $characterGrid = $this->splitLinesIntoCharacters($lines);
        $transposedLines = [];

        foreach (array_keys($characterGrid[0]) as $column) {
            $transposedLines[] = implode('', array_column($characterGrid, $column));
        }

        return $transposedLines;
    }

    /**
     * [[a, b, c],      [[d, a],
     *  [d, e, f]]  =>   [e, b],
     *                   [f, c]].
     *
     * @template T
     *
     * @param list<list<T>> $grid
     *
     * @return list<list<T>>
     */
    public function rotateGridClockwise(array $grid): array
    {
        if ([] === $grid) {
            return [];
        }

        $rotatedGrid = [];

        // each new row is an old column, read from bottom to top
        foreach (array_keys($grid[0]) as $column) {
            $rotatedGrid[] = array_reverse(array_column($grid, $column));
        }

        return $rotatedGrid;
    }

    /**
     * [[a, b, c],      [[c, f],
     *  [d, e, f]]  =>   [b, e],
     *                   [a, d]].
     *
     * @template T
     *
     * @param list<list<T>> $grid
     *
     * @return list<list<T>>
     */
    public function rotateGridCounterClockwise(array $grid): array
    {
        $rotatedGrid = [];

        // each old row, read from right to left, fills the new rows top to bottom
        foreach ($grid as $row) {
            foreach (array_reverse($row) as $newRowIndex => $cell) {
                $rotatedGrid[$newRowIndex][] = $cell;
            }
        }

        return $rotatedGrid;
    }
}
