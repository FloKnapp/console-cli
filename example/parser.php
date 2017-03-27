<?php

require_once __DIR__ . '/../vendor/autoload.php';

$console = new \ConsoleCli\Console($argv);

echo $console->getController();

echo $console->getOpt('z');