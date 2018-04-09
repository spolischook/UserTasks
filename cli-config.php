<?php

require 'vendor/autoload.php';

use App\Command\SchemaCommand;
use App\Kernel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\ConsoleRunner;

$kernel = new Kernel('dev');
$container = $kernel->createContainer();

// You can append new commands to $commands array, if needed
$commands = [
    new SchemaCommand($kernel),
];

return ConsoleRunner::createHelperSet($container->get(Connection::class));
