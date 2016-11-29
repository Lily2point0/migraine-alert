<?php

namespace Ultra\REST;

class Request {
	public function __construct($url = null, $verb = null, $postData = null) {
		$this->url = parse_url($url ? $url : $_SERVER['REQUEST_URI']);
		$this->postData = $postData ? $postData : file_get_contents('php://input');
		$this->verb = $verb ? $verb : "GET";
	}
	
	public function getVerb() {
		return $this->verb;
	}
	
	public function getPostData() {
		return $this->postData;
	}

	public function hasParameters() {
		if (!isset($this->url['query'])) {
			return false;
		}
		return $this->url['query'] !== '' ? true : false;
	}
	
	public function getParameter($name, $defaultValue = null) {
		$queryList = explode("&", @$this->url['query']);

		foreach($queryList as $query) {
			if (strpos($query, "=") == false && $query == $name) {
				return $query;
			}
			@list($key, $value) = explode("=", $query);
			if ($key == $name) {
				return urldecode($value);
			}
		}

		return $defaultValue;
	}
	
	public function getPageName() {
		return $this->url['path'];
	}
	
	public function getNamedAnchor() {
		return @$this->url['fragment'];
	}
	
}

