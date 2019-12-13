<?php
include_once 'init.php';
require WATCHTOWER_DROOT.'/vendor/autoload.php';
use WatchTower\WatchTower;
echo WatchTower::getInstance()->getBox($_REQUEST);