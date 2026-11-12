<?php

declare(strict_types=1);

namespace App\Service;

class InputParser
{
    public function parseInputWithIntegersAndComma(array $lines)
    {
        $finalArray = [];
        foreach ($lines as $key => $line) {
            $arrLine = explode(',', $line);
            foreach ($arrLine as $keyLine => $number) {
                $arrLine[$keyLine] = (int) $number;
            }
            $finalArray[$key] = $arrLine;
        }

        return $finalArray;
    }

    // a bc turns to ['a',' ','b','c']
    public function parseInputFromStringsToArray(array $lines)
    {
        $finalArray = [];

        foreach ($lines as $key => $line) {
            $arrLine = str_split($line);
            $finalArray[$key] = $arrLine;
        }

        return $finalArray;
    }

    // a bc turns to ['a', 'bc']
    public function parseInputFromStringsWithSpaceToArray(array $lines)
    {
        $finalArray = [];

        foreach ($lines as $key => $line) {
            $arrLine = explode(' ', $line);
            $finalArray[$key] = $arrLine;
        }

        return $finalArray;
    }

    public function parseInputFromStringsToIntArray(array $lines)
    {
        $finalArray = [];

        foreach ($lines as $key => $line) {
            $arrLine = array_map('intval', str_split($line));
            $finalArray[$key] = $arrLine;
        }

        return $finalArray;
    }

    public function getReversedGrid(array $grid): array
    {
        $fullArrayGrid = [];
        $reversedGrid = [];
        foreach ($grid as $key => $line) {
            $fullArrayGrid[$key] = str_split($line);
        }

        for ($i = 0; $i < count($fullArrayGrid[0]); ++$i) {
            $reversedGrid[$i] = implode('', array_column($fullArrayGrid, $i));
        }

        return $reversedGrid;
    }

    public function rotateGridClockwise(array $grid): array
    {
        $grid = array_values($grid);
        $result = [];

        // make each new row = reversed old column
        foreach (array_keys($grid[0]) as $column) {
            $result[] = array_reverse(array_column($grid, $column));
        }

        return $result;
    }

    public function rotateGridAntiClockwise(array $grid): array
    {
        foreach ($grid as $row) {
            foreach (array_reverse($row) as $i => $v) {
                $result[$i][] = $v;
            }
        }

        return $result;
    }
}
