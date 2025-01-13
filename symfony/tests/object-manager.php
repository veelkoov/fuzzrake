<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/../.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']); // @phpstan-ignore argument.type (Code from https://github.com/phpstan/phpstan-doctrine)
$kernel->boot();

$objectManager = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

return $objectManager;
