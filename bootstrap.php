<?php
define('DS',DIRECTORY_SEPARATOR);
$documentRoot = str_replace(['\\','/'],[DS,DS], realpath(__DIR__));
$sourceRoot = str_replace(['\\','/'],[DS,DS], realpath(__DIR__) . '/src');
$testRoot = str_replace(['\\','/'],[DS,DS], realpath(__DIR__) . '/tests');
$webRoot = 'http://' . $_SERVER['SERVER_NAME'];
$loader = require 'vendor/autoload.php';
$loader->addPsr4('WatchTower\\', $sourceRoot);
$loader->addPsr4('WatchTower\\Tests\\', $testRoot);

/** @const string DR document root */
define('DR',$documentRoot);

/** @const string SR source root */
define('SR',$sourceRoot);

/** @const string TR test root */
define('TR',$testRoot);

/** @const string WR web root */
define('WR',$webRoot);