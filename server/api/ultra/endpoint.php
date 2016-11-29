<?php

namespace Ultra\REST;

class Endpoint {
	public function __construct($request) {
		$this->request = $request;
	}
	
	public function process() {
		$id = $this->request->getParameter('id');

		if ($this->request->getParameter('new')) {	// create new item
			return $this->createNew($this->request);

		} else if ($id && $this->request->getVerb() == 'GET') {	// get data for ID $anchor
			return $this->getData($id, $this->request);

		} else if ($id && $this->request->getVerb() == 'POST') {	// update data for ID $anchor
			return $this->updateData($id, $this->request);

		} else if ($this->request->getParameter('list') || !$this->request->hasParameters()) {
			return $this->listData($this->request);
		}

		return null;
	}
	
	public function createNew($request) {
	}
	
	public function getData($id, $request) {
	}
	
	public function updateData($id, $data) {
		
	}
	
	public function listData($request) {
		$this->endpoint = $endpoint;
	}
	

}

