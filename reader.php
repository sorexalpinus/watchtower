<?php
include_once 'init.php';
require WATCHTOWER_DROOT.'/vendor/autoload.php';
use WatchTower\WatchTower;
echo WatchTower::create([])->getBox($_REQUEST);