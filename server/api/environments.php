<?php

require_once 'config/db.php';
require_once 'config/system.php';
require_once 'base.php';
require_once 'ultra/ultra.php';

$api = new BasicAPI($_GET);
$api->connect($gConfigDB);

$u = new \Ultra\REST\BasicRecord("environments", $api, new \Ultra\REST\Request());
$u->process();

?>
