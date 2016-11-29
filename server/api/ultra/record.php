<?php

namespace Ultra\REST;

class BasicRecord extends \Ultra\REST\Endpoint {

	public function __construct($tableName, $api, $request) {
		PARENT::__construct($request);

		$this->table_name = $tableName;
		$this->api = $api;
		$this->machine_token = $this->api->getMachineIDFromToken();
	}

	public function createNew($request) {
		$record = \R::dispense($this->table_name);
		$record->name = $request->getParameter("name", "No name");
		$record->token = uniqid();
		$record->machine_token = $this->machine_token;

		$id = \R::store($record);

		$this->api->success("Registered new entry, " . $record->name, $this->presentData($record));
	}

	public function getData($id, $request) {
		$record = \R::findOne($this->table_name, "token = ? AND machine_token = ?", [$id, $this->machine_token]);
		if ($record) {
			$this->api->success("Data found", $this->presentData($record));
		} else {
			$this->api->errorNotFound();
		}
	}

	public function listData($request) {
		$record = \R::find($this->table_name, "machine_token = ?", [$this->machine_token]);
		$results = array();
		foreach($recordList as $record) {
			array_push($results, $this->presentData($record));
		}

		$this->api->success("User data found", $results);
	}

	protected function presentData($record) {
		return array('token' => $record->token, 'name' => $record->name);
	}
}
 
