<?php
//echo '<pre>';
//print_r(__DIR__);
//echo '</pre>';
//die;
\define('WATCHTOWER_DROOT',str_replace(['\\','/'],['/','/'], realpath(__DIR__)));
\define('WATCHTOWER_SROOT',WATCHTOWER_DROOT . '/src');
\define('WATCHTOWER_TROOT',WATCHTOWER_DROOT . '/tests');
\define('WATCHTOWER_FROOT',WATCHTOWER_DROOT . '/files');
