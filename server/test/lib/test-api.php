<?php
namespace Ultra\Test;

class API {
var $url;

	public function __construct($baseURL) {
		$this->url = $baseURL;
	}


	public function makePostTest($description, $testFunction, $cbPrepare, $cbSuccess) {
		
		$api = $this->url . $testFunction;

		$test = new TestAPI($description, $this->url, function($testargs) {
			$test = $testargs['self'];
			if ($test->prepare) {
				call_user_func($test->prepare, $test);
			}

			try {
				$ch = $test->makeCurlRequest(true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $test->getData());

				$response = curl_exec($ch);

				curl_close($ch);
			} catch (Exception $e) {
				// we failed :(		
				$response = '{}';
			}

			$success_callback = $testargs['success_callback'];
			$json = json_decode($response);

			return call_user_func($success_callback, $json);
		}, array('api' => $api, 'success_callback' => $cbSuccess));
	
		$test->setSelf($test);
		$test->prepare = $cbPrepare;

		return $test;
	}

	public function makeGetTest($description, $testFunction, $cbPrepare, $cbSuccess) {
		$api = $this->url . $testFunction;

		$test = new TestAPI($description, $this->url, function($testargs) {
			$test = $testargs['self'];
			if ($test->prepare) {
				call_user_func($test->prepare, $test);
			}

			$success_callback = $testargs['success_callback'];

			try {
				$ch = $test->makeCurlRequest(true);

				$response = curl_exec($ch);

				curl_close($ch);
			} catch (Exception $e) {
				// we failed :(	
				$response = '{}';			
			}

			$json = json_decode($response);

			return call_user_func($success_callback, $json);
		}, array('api' => $api, 'success_callback' => $cbSuccess));

		$test->setSelf($test);
		$test->prepare = $cbPrepare;

		return $test;
	}

}

class TestAPI extends Test {
var $url;
var $token;
var $fnparams;

	public function __construct($description, $baseURL, $cbfn, $fnarguments = "") {
		$this->url = $baseURL;
		$this->fnparams = array();

		parent::__construct($description, $cbfn, $fnarguments);
	}


	public function setSelf($self) {
		$this->fnarguments['self'] = $self;
	}


	public function setURL($url) {
		$this->fnarguments['api'] = $this->url . $url;
	}

	public function setURLParameter($name, $value) {
		$this->fnparams[$name] = $value;
	}

	public function verifyWithToken($token) {
//		$this->fnparams['jwt_token'] = $token;
		$this->token = $token;
	}

	public function verifyAPI($token, $secret) {
		$this->fnparams['token'] = $token;
		$this->fnparams['secret'] = $secret;
	}

	public function getFullURL() {
		$url = $this->fnarguments['api'] . '?';
		$url.= http_build_query($this->fnparams);
		
		return $url;
	}

	public function setData($data) {
		$this->fnarguments['data'] = $data;
	}

	public function getData() {
		return $this->fnarguments['data'];
	}

	public function makeCurlRequest($isPost) {
		$url = $this->getFullURL();
		$ch = curl_init($url);

		$header = array('Content-Type: text/plain');
		if (isset($this->token)) {
			array_push($header, 'Auth: Bearer ' . $this->token);
		}

		curl_setopt($ch, CURLOPT_POST, $isPost ? 1 : 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		return $ch;
	}
}

?>
