<?php

require_once 'config/db.php';
require_once 'config/system.php';
require_once 'base.php';

$api = new BasicAPI($_GET);
$api->connect($gConfigDB);

if ($api->isUnregistered()) {
	$token = $api->getParameter("token");

	if ($gConfigSystem['final'] || in_array($token, $gConfigSystem['valid_machine_list'])) {
		$machine = \R::dispense('machine');
		$machine->token = $api->getParameter("token");
		$machine->secret = uniqid();
		$machine->verified = false;
	
		$id = \R::store($machine);

		$api->success("Registered", array('secret'=>$machine->secret));
	} else {
		$api->error("You are not authorized to use this service", 401);
	}
}

?>
