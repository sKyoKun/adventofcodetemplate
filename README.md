# Adventofcode2025

This repository contains all the code written for https://adventofcode.com/2025. These are Christmas themed exercices that you can answer in any language. Mine is made in PHP/Symfony7

I try to be as clean as possible but sometimes time is leaving me no choice than "quick and dirty" (sorry !) 

## :hammer: Initialize the project

Install the project : ```composer install```

Run the symfony server : ```symfony serve -d```

## :books: How to use
Initialize a new day : ```php bin/console generate:day X``` where X is the day number

- Create your real input file in ```inputs/dayX.txt```
- If needed, create your test file in ```inputs/dayXtest.txt```
- Call **/dayX/1** for the first part, **/dayX/2** for the second part to execute the real input
- Call **/dayX/1/dayXtest** to execute with the test input

For the leaderboards, route is /leaderboards/{year} :

Configure the API calls :
- Create a cookie.txt file at the root of the project and paste the session cookie into it. To do so, go to an AOC page and use the network tab of the developer tool to retrieve the session cookie.
- Paste it in the cookie.txt file (/!\ remove the blank line if it exists)
- Update the default year on the controller if needed
- Create a .env.local file with an array of the different leaderboard you're a member of ```LEADERBOARDS='[{"name":"leaderboard name","id":12345}]```

## :wrench: Useful commands 
Run php-cs-fixer : ```vendor/bin/php-cs-fixer fix```

Run the tests : 
```vendor/bin/phpunit``` (unit)
```vendor/bin/behat``` (functional)
