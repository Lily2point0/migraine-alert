<?php
header("Access-Control-Allow-Origin: *");

require 'lib/rb.php';
require 'lib/jwt_helper.php';

class BasicAPI {
var $paramlist;

	public function __construct($params) {
		$this->paramlist = $params;

		if (function_exists('getallheaders')) {
			$headers = getallheaders();
			if (isset($headers['Auth'])) {
				$auth = $headers['Auth'];
				list($type, $token) = explode(" ", $auth);
				$this->token = trim($token);
			}
		}
	}

	public function connect($config) {
		\R::setup( 'mysql:host=localhost;dbname=' . $config['dbname'], $config['username'], $config['password'] );
		\R::debug(FALSE);
		\R::setAutoResolve(TRUE);

		if (isset($this->token)) {
			$this->jwt_token = JWT::decode($this->token, $config['jwt_secret']);
		}
	}

	public function getParameter($name, $defaultName = "") {
		if (is_array($name)) {
			foreach($name as $n) {
				if (isset($this->paramlist[$n])) {
					return $this->paramlist[$n];
				}
			}
			return null;
		}
		return @$this->paramlist[$name] ?: $defaultName;
	}

	public function error($code, $reason) {
		$this->report(array('success' => 0, 'description' => $reason, 'error_code'=>$code, 'result' => array()));
	}

	public function success($reason, $result = array()) {
		$this->report(array('success' => 1, 'description' => $reason, 'result' => $result));
	}

	public function report($r) {
		print json_encode($r);
	}

	// Helpers for errors
	public function errorUnauthorized() {
		return $this->error(401, "You are not authorized to access this resource.");
	}

	public function errorNotFound() {
		return $this->error(404, "This resource is not found.");
	}


	public function getMachine() {
		$token = $this->getParameter("token");
		return \R::findOne('machine', "token='$token'");
	}

	public function getMachineFromToken() {
		$token = $this->jwt_token->machine_id;
		return \R::findOne('machine', "token='$token'");
	}

	public function getMachineIDFromToken() {
		return $this->jwt_token->machine_id;
	}

	public function isUnregistered() {

		$machine = $this->getMachine();
		$secret = $this->getParameter("secret");

		if ($machine == null) {
			return true;
		}

		$this->error(100, "Machine is already registered.");
		return false;
	}


	public function isRegisteredButUnverified() {

		$machine = $this->getMachine();
		$secret = $this->getParameter("secret");

		if ($machine == null) {
			$this->error(101, "Machine is not yet registered.");

		} else if ($machine['secret'] != $secret) {
			$this->error(102, "Invalid machine secret.");

		} else if ($machine['verified']) {
			$this->error(103, "This machine is already verified.");
		} else {
			return true;			
		}

		return false;
	}

	public function isTokenVerified() {
		return isset($this->jwt_token) ? true : false;
	}

	public function isVerified() {

		$machine = $this->getMachine();
		$secret = $this->getParameter("secret");

		if ($machine == null) {
			$this->error(101, "Machine is not yet registered.");

		} else if ($machine['secret'] != $secret) {
			$this->error(102, "Invalid machine secret.");

		} else if (!$machine['verified']) {
			$this->error(103, "Machine has not been verified yet.");
		} else {
			return true;
		}

		return false;
	}
}
