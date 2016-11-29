<?php

require_once 'config/db.php';
require_once 'config/system.php';
require_once 'base.php';
require_once 'lib/jwt_helper.php';

$api = new BasicAPI($_GET);
$api->connect($gConfigDB);

if ($api->isRegisteredButUnverified()) {
	$machine = $api->getMachine();

	$token = array();
	$token['machine_id'] = $machine->token;

	$jwt_token = JWT::encode($token, $gConfigDB['jwt_secret']);
	$machine->verified = true;

	$id = \R::store($machine);

	$api->success("Account now verified $id", array('token'=>$jwt_token));
}

?>
