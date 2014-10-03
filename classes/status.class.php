<?php

class Status {
	// Creating some properties (variables tied to an object)
	public $id;
	public $message;
	
	// Assigning the values
	public function __construct($id, $message) {
		$this->id = $id;
		$this->message = $message;
	}
	
	// Creating a method (function tied to an object)
	public function sayHello() {
		return "Hello, my status id is " . $this->id . " " . $this->message . ". Nice to meet you! :-)";
	}

	public function getStatusCode() {
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
		return $this->id;
	}

	public function setMessage($message){
		$this->message = $message;
		return $this->message;
	}
}

?>