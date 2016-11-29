<?php

require 'lib/ultra-test.php';
require 'lib/test-api.php';

/*
EXAMPLE:

$api = new \Ultra\Test\API("http://marquisdegeek.com/api/tube/");

$testing = new \Ultra\Test\TestSuite();
$testing->addTest($api->makeGetTest("?sortby=distance&limit=1", function($result) {
	return count($result) == 1 && $result[0]->name == "Abbey Road"? true : false;
}, "Register machine"));

$testing->addTest(new \Ultra\Test\TestPass());
$testing->addTest(new \Ultra\Test\TestFail());

$testing->test();
*/


$api = new \Ultra\Test\API("http://52.16.55.190/api/");

$gToken = uniqid();
$gSecret = '';
$gAPIToken = '';
$gTokenUser = '';
$gTokenEnv = '';

$apiRegisterTest = $api->makeGetTest("Register machine", "register.php", 
	function($test) {
		global $gToken;

		$test->setURLParameter('token', $gToken);

	}, function($result) {
		global $gSecret;

		$gSecret = $result->result->secret;

		return $result->success == 1 ? true : false;
	});


$apiVerifyTest = $api->makeGetTest("Verify machine", "tokens.php", 
	function($test) {
		global $gToken, $gSecret;

		$test->verifyAPI($gToken, $gSecret);
		$test->setURLParameter('new', 'new');

	}, function($result) {
		global $gAPIToken;

		$gAPIToken = $result->result->token;
		return $result->success == 1 ? true : false;
	});


$apiCreateEnvironmentTest = $api->makeGetTest("Create new environment", "environments.php", 
	function($test) {
		global $gAPIToken;

		$test->verifyWithToken($gAPIToken);
		$test->setURLParameter('new', 'new');
		$test->setURLParameter('name', "Test env");

	}, function($result) {
		global $gTokenEnv;
		$gTokenEnv = $result->result->token;

		return $result->success == 1  && $result->result->name == 'Test env' ? true : false;
	});


$apiCreateUserTest = $api->makeGetTest("Create new user", "users.php", 
	function($test) {
		global $gAPIToken;

		$test->verifyWithToken($gAPIToken);
		$test->setURLParameter('new', 'new');
		$test->setURLParameter('name', "TestyMcTestface");

	}, function($result) {
		global $gTokenUser;

		$gTokenUser = $result->result->token;
		return $result->success == 1 && $result->result->name == 'TestyMcTestface' ? true : false;
	});


$apiPostTest = $api->makePostTest("Sync data", "data.php",
	function($test) {
		global $gTokenEnv, $gTokenUser;
		global $gAPIToken;

		$test->verifyWithToken($gAPIToken);

		$sync_data = array(
			'environment' => array('token'=>$gTokenEnv, 'data'=>array()),
			'user' => array('token'=>$gTokenUser, 'environment'=>$gTokenEnv, 'data'=>array())
			);
		for($i=0;$i<10;++$i) {
			$d = array('timestamp'=>time()+$i*60, 'light'=>rand(40,80), 'temp'=>rand(20,30), 'sound'=>rand(10,80));
			array_push($sync_data['environment']['data'], $d);

			$u = array('timestamp'=>time()+$i*60, 'migraine'=>(($i%3) == 0 ? 50 : 0));
			array_push($sync_data['user']['data'], $u);
		}

		$test->setURLParameter('new', 'new');
		$test->setData(json_encode($sync_data));

	}, function($result) {
		return $result->success == 1 ? true : false;
	});


$apiResultsTest = $api->makeGetTest("Get results", "data.php", 
	function($test) {
		global $gTokenUser, $gTokenEnv;
		global $gAPIToken;

		$endtime = time() + 60*10;

		$test->verifyWithToken($gAPIToken);

		$test->setURLParameter('list', 'list');
		$test->setURLParameter('user', $gTokenUser);
		$test->setURLParameter('environment', $gTokenEnv);
		$test->setURLParameter('from', 0);
		$test->setURLParameter('until', $endtime);

	}, function($result) {
		$entriesUser = 0;
		foreach($result->result->user as $user_record) {
			++$entriesUser;
		}

		$entriesEnv = 0;
		foreach($result->result->environment as $env_record) {
			++$entriesEnv;
		}
		return $result->success == 1 && $entriesUser == 10 && $entriesEnv == 10 ? true : false;
	});


$testing = new \Ultra\Test\TestSuite();
$testing->addTest($apiRegisterTest);

$testing->addTest($apiVerifyTest);
$testing->addTest($apiCreateUserTest);
$testing->addTest($apiCreateEnvironmentTest);
$testing->addTest($apiPostTest);
$testing->addTest($apiResultsTest);

$testing->test();

print "For reference: " . PHP_EOL;
print "   gToken = $gToken" . PHP_EOL;
print "   gSecret = $gSecret " . PHP_EOL;
print "   gAPIToken = $gAPIToken" . PHP_EOL;
print "   gTokenUser = $gTokenUser" . PHP_EOL;
print "   gTokenEnvironment = $gTokenEnv" . PHP_EOL;

?>
