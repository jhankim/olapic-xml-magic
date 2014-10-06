<?php

class Status {
	// Creating some properties (variables tied to an object)
	public $code;
	public $message;
	
	// Assigning the values
	public function __construct($code, $message) {
		$this->code = $code;
		$this->message = $message;
	}
	
	// Creating a method (function tied to an object)
	public function sayHello() {
		return "Hello, my status id is " . $this->id . " " . $this->message . ". Nice to meet you! :-)";
	}

	public function getStatusCode() {
		return $this->code;
	}

	public function setCode($code){
		$this->code = $code;
		switch ($code) {
		    case 1:
		        $this->setMessage('Wooohooo! Validation passed :) Please check hierarchy below!');
		        break;
		    case 2:
		        $this->setMessage('Document not valid');
		        break;
		    case 3:
		        $this->setMessage('Document not well-formed');
		        break;
	        case 4:
	        	$this->setMessage('Document not valid & well-formed');
	        	break;
	        case 5:
	        	$this->setMessage('URL Invalid or Auth failed');
	        	break;
	        case 6:
	        	$this->setMessage('Misc error');
	        	break;
		}
		return $this->code;
	}

	public function setMessage($message){
		$this->message = $message;
		return $this->message;
	}
}

?>