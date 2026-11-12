# Advent of Code template

This repository is my template to solve https://adventofcode.com in PHP/Symfony 8.1. These are Christmas themed exercices that you can answer in any language. Each year, I create a new repository from this template (e.g. adventofcode2025).

I try to be as clean as possible but sometimes time is leaving me no choice than "quick and dirty" (sorry !) 

## :hammer: Initialize the project

Requirements : PHP 8.4+ with the `bcmath`, `ctype`, `iconv`, `intl`, `mbstring` and `xml` extensions, Composer and the Symfony CLI.

Install the project : ```composer install```

Run the symfony server : ```symfony serve -d```

## :christmas_tree: Start a new year

- Create a new repository from this template
- Set the year in ```.env``` : ```AOC_YEAR=2026```

## :books: How to use
Initialize a new day : ```php bin/console generate:day X``` where X is the day number (1 to 25, there are only 12 days since 2025)

It creates the controller, the service, their tests and the input files of the day. Existing files are never overwritten.

- Paste your real input in ```inputs/dayX.txt``` (not versioned)
- Paste the example of the puzzle in ```inputs/dayXtest.txt```
- Call **/dayX/1** for the first part, **/dayX/2** for the second part to execute the real input
- Call **/dayX/1/dayXtest** to execute with the test input
- Write the expected answers of the example in ```features/dayX.feature``` and the tests of your service in ```tests/Service/DayXServiceTest.php```

To read and parse the input :
- ```InputReader::getInput('dayX.txt')``` returns one trimmed line per entry, blank lines included. Use ```getInput('dayX.txt', trimLines: false)``` when leading spaces matter (drawings, aligned columns)
- ```InputParser``` turns these lines into the usual structures : ```parseCommaSeparatedIntegers```, ```splitLinesIntoCharacters```, ```splitLinesBySpace```, ```splitLinesIntoDigits```, ```transposeGrid```, ```rotateGridClockwise```, ```rotateGridCounterClockwise```

## :trophy: Leaderboards
Route is **/leaderboards** for the year set in ```AOC_YEAR```, or **/leaderboards/{year}** for another year.

Configure the API calls :
- Create a cookie.txt file at the root of the project and paste the session cookie into it. To do so, go to an AOC page and use the network tab of the developer tool to retrieve the session cookie.
- Create a .env.local file with an array of the different leaderboards you're a member of : ```LEADERBOARDS='[{"name":"leaderboard name","id":12345}]'```
- To display several leaderboards as one, give them the same group : ```LEADERBOARDS='[{"name":"WA Lyon","id":12345,"group":"WA"},{"name":"WA Paris","id":67890,"group":"WA"}]'```

Advent of Code asks not to call its API more than once every 15 minutes : each leaderboard is cached for 30 minutes (clearing the Symfony cache also clears it).

## :wrench: Useful commands 
Run php-cs-fixer : ```vendor/bin/php-cs-fixer fix```

Run the tests : 
```vendor/bin/phpunit``` (unit)
```vendor/bin/behat``` (functional)
