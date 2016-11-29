<?php

require_once 'config/db.php';
require_once 'config/system.php';
require_once 'base.php';
require_once 'ultra/ultra.php';

$api = new BasicAPI($_GET);
$api->connect($gConfigDB);

class DataEndpoint extends \Ultra\REST\Endpoint {

	public function __construct($api, $request) {
		PARENT::__construct($request);
		$this->api = $api;
	}

        public function createNew($request) {
		$machine = $this->api->getMachineFromToken();

		$data = $request->getPostData();
		$json = json_decode($data);

		// The data is in two parts: environment, and user

		//
		// Environment
		//
		$envData = $json->environment->data;
		$envToken = $json->environment->token;
	
		for($i=0;$i<count($envData);++$i) {
			$envDBObject = \R::dispense('envdata');
			$envDBObject->environment = $envToken;	
			$envDBObject->timestamp = $envData[$i]->timestamp;
			$envDBObject->light = $envData[$i]->light;
			$envDBObject->temp = $envData[$i]->temp;
			$envDBObject->sound = $envData[$i]->sound;
	
			$machine->ownEnvironmentList[] = $envDBObject;
		
			\R::store($envDBObject);
		}
	
		//
		// User
		//
		$userData = $json->user->data;
		$userToken = $json->user->token;
	
		for($i=0;$i<count($userData);++$i) {
			$envDBObject = \R::dispense('userdata');
			$envDBObject->token = $userToken;	
			$envDBObject->environment = $envToken;	
			$envDBObject->timestamp = $userData[$i]->timestamp;
			$envDBObject->migraine = $userData[$i]->migraine;
	
			$machine->ownEnvironmentList[] = $envDBObject;
	
			\R::store($envDBObject);
		}
		
		\R::store($machine);

		$this->api->success("Data sync complete", "");
        }

        public function getData($id, $request) {
	}

        public function listData($request) {
		$machine = $this->api->getMachineFromToken();

		$user = $request->getParameter('user');
		$environment = $request->getParameter('environment');
	
		$timeFrom = $request->getParameter('from', 0);
		$timeUntil = $request->getParameter('until', time());
	
		$results = array();
		$results['request'] = array('user'=>$user, 'environment'=>$environment, 'from'=>$timeFrom, 'until'=>$timeUntil);
	
		$results['environment'] = array();
		$envdata = \R::find('envdata', 'environment = ? and timestamp>? and timestamp<=?', [$environment, $timeFrom, $timeUntil]);
	
		// Convert the object of objects into an array of objects to help client side
		foreach($envdata as $id =>$datum) {
			array_push($results['environment'], $datum);
		}
		
		$results['user'] = array();
		$userdata = \R::find('userdata', 'token = ? and timestamp>? and timestamp<?', [$user, $timeFrom, $timeUntil]);
	
		// Convert the object of objects into an array of objects to help client side
		foreach($userdata as $id =>$datum) {
			array_push($results['user'], $datum);
		}
		
	
		$this->api->success("Results", $results);
        }

        public function updateData($id, $data) {

        }

}

if ($api->isTokenVerified()) {
	$u = new DataEndpoint($api, new \Ultra\REST\Request());
	$u->process();
} else {
        $api->errorUnauthorized();
}

?>
