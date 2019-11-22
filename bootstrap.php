<?php
define('DS',DIRECTORY_SEPARATOR);
$documentRoot = str_replace(['\\','/'],[DS,DS], realpath(__DIR__));
$sourceRoot = str_replace(['\\','/'],[DS,DS], realpath(__DIR__) . '/src');
$testRoot = str_replace(['\\','/'],[DS,DS], realpath(__DIR__) . '/tests');
$loader = require 'vendor/autoload.php';
$loader->addPsr4('WatchTower\\', $sourceRoot);
$loader->addPsr4('WatchTower\\Tests\\', $testRoot);