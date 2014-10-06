<?php

class Product {
	// Creating some properties (variables tied to an object)
	public $isParent = true;
	public $id;
	public $name;
	public $url;
	public $imageUrl;
	public $parentId;
	public $color;
	public $children = array();
	
	// Assigning the values
	public function __construct($id, $name, $url, $imageUrl, $parentId, $color) {
		$this->id = $id;
		$this->name = $name;
		$this->url = $url;
		$this->imageUrl = $imageUrl;
		$this->parentId = $parentId;
		$this->color = $color;
	}
	
	// Creating a method (function tied to an object)
	public function sayHello() {
		return "Hello, my name is " . $this->name . " " . $this->imageUrl . ". Nice to meet you! :-)";
	}

	public function getImageUrl() {
		return $this->imageUrl;
	}

	public function setChildren($childObj) {
		$this->children[] = $childObj;
		return $this->children; 
	}

	public function setToChild(){
		$this->isParent = false;
		return $this->isParent;
	}
}

?>