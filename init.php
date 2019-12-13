<?php
define('WATCHTOWER_DROOT',str_replace(['\\','/'],['/','/'], realpath(__DIR__)));
define('WATCHTOWER_SROOT',WATCHTOWER_DROOT . '/src');
define('WATCHTOWER_TROOT',WATCHTOWER_DROOT . '/tests');
define('WATCHTOWER_FROOT',WATCHTOWER_DROOT . '/files');
define('WATCHTOWER_RROOT',WATCHTOWER_DROOT . '/resources');
