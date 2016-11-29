<?php
namespace Ultra\Test;

class TestSuite {
	var $testList;

	public function __construct() {
		$this->testList = array();
	}

	public function addTest($test) {
		array_push($this->testList, $test);
	}

	public function test() {
		$passed = 0;
		foreach($this->testList as $test) {
			$passed += $test->invoke();
		}

		print PHP_EOL;
		print "Passed $passed out of " . count($this->testList);
		print PHP_EOL;

		return $passed;
	}
}


class Test {
	
	public function __construct($description, $cbfn, $fnarguments = "") {
		$this->cbfn = $cbfn;
		$this->fnarguments = $fnarguments;
		$this->description = $description;
	}

	public function invoke() {
		print $this->description . "... ";
		$success = call_user_func($this->cbfn, $this->fnarguments);
		print $success ? "\033[0;32m Passed" : "\033[0;31m Failed";
		print "\033[0m " . PHP_EOL;

		return $success;
	}

	public function getArguments() {
		return $this->fnarguments;
	}

	public function setArguments($newargs) {
		$this->fnarguments = $newargs;
	}


}


class TestPass extends Test {
	public function __construct() {
		parent::__construct(function() { return true; }, array(), "Always passes");
	}
}

class TestFail extends Test {
	public function __construct() {
		parent::__construct(function() { return false; }, array(), "Always fails");
	}
}



?>
