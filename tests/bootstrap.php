<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    // "test" by default: Behat, unlike PHPUnit, does not set APP_ENV before booting
    new Dotenv()->bootEnv(dirname(__DIR__).'/.env', 'test');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0o000);
}
