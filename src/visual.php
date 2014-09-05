<?php 

opcache_reset();


ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

ini_set('memory_limit', '-1');

$localArray = array();

class Product {
	// Creating some properties (variables tied to an object)
	public $isParent = true;
	public $id;
	public $name;
	public $url;
	public $imageUrl;
	public $parentId;
	public $children = array();
	
	// Assigning the values
	public function __construct($id, $name, $url, $imageUrl, $parentId) {
		$this->id = $id;
		$this->name = $name;
		$this->url = $url;
		$this->imageUrl = $imageUrl;
		$this->parentId;
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

function getDirectory( $path = '.', $level = 0 ){ 

	$ignore = array( 'cgi-bin', '.', '..', '.git', '.DS_Store' ); 
	// Directories to ignore when listing output. Many hosts 
	// will deny PHP access to the cgi-bin. 

	// print_r($path);die();    

	$dh = opendir( $path ); 
	// Open the directory to the handle $dh 

	while( false !== ( $file = readdir( $dh ) ) ){ 
	// Loop through the directory 
	 
		if( !in_array( $file, $ignore ) ){ 
		// Check that this file is not to be ignored 
			 
			$spaces = str_repeat( '&nbsp;', ( $level * 4 ) ); 
			// Just to add spacing to the list, to better 
			// show the directory tree. 
			echo "<ul>";
			if( is_dir( "$path/$file" ) ){ 
			// Its a directory, so we need to keep reading down... 
				// echo "<li><strong>" . $spaces . $file . "</strong></li>"; 
				echo "<li><strong>" . $file . "</strong></li>"; 
				getDirectory( "$path/$file", ($level+1) ); 
				// Re-call this same function but on a new directory. 
				// this is what makes function recursive. 
			 
			} else { 
			 
				echo "<li><a href=\"?convert=$path/$file\">$file</a></li>"; 
				// Just print out the filename 
			 
			} 
			echo "</ul>";
		 
		} 
	 
	} 
	 
	closedir( $dh ); 
	// Close the directory handle 

} 

echo "<div style=\"float:left;height:500px;overflow-y:scroll\">";

getDirectory('.'); 

echo "</div>";
?>

<html>
<body>

<?php if (empty($_GET["convert"])) { ?>

Please select a file:</br>
<form action="xsdvalidate.php">
URL: <input type="text" name="convert"><br>
<input type="submit">
</form>

<?php 

	die(); }

	$xmlfile = htmlspecialchars($_GET["convert"]);
	$xsdfile = 'http://photorank.me/olapicProductFeedV1_0.xsd';

	libxml_use_internal_errors(true);
 
	$feed = new DOMDocument();
	$feed->preserveWhitespace = false;
	$result = $feed->load($xmlfile);
	if($result === TRUE) {
		echo "<img src=\"http://www.lab.westilian.com/bingo/01/blue/images/checked.png\" />Document is well formed<br>";
	} else {
		echo "<img src=\"http://www.lab.westilian.com/bingo/01/blue/images/alert.png\" />Document is not well formed<br>";
	}
	if(@($feed->schemaValidate($xsdfile))) {
		echo "<img src=\"http://www.lab.westilian.com/bingo/01/blue/images/checked.png\" /> Document is valid!";
	} else {
		echo "<img src=\"http://www.lab.westilian.com/bingo/01/blue/images/alert.png\" />Document is not valid<br>";
		// var_dump the error messages
		$errors = libxml_get_errors();

		echo "Total number of errors: " . count($errors) . "\n";

		echo "<br><textarea style=\"height:400px;width:400px;\" disabled>";
		foreach($errors as $error) {
			echo "\n---\n";
			printf("Error: %sLine: %s, column: %s, level: %s, code: %s",
				$error->message,
				$error->line,
				$error->column,
				$error->level,
				$error->code
			);
			echo "\n---\n";
		}
		echo "</textarea>";
	}

	if (@($feed->schemaValidate($xsdfile)) && $result === TRUE) {
		$xml = simplexml_load_file('feed.xml','SimpleXMLElement', LIBXML_NOCDATA);
		$json = json_encode($xml,JSON_PRETTY_PRINT);
		$array = json_decode($json,TRUE);

		$max = sizeof($array['Products']['Product']);

		for ($i = 0; $i < 100 ; $i++) {
			// print_r($array['Products']['Product'][$i]);

			$productId = $array['Products']['Product'][$i]['ProductUniqueID'];
			$productName = $array['Products']['Product'][$i]['Name'];
			$productUrl = $array['Products']['Product'][$i]['ProductUrl'];
			$productImageUrl = $array['Products']['Product'][$i]['ImageUrl'];
			$parentId = (isset($array['Products']['Product'][$i]['ParentID']) ? $array['Products']['Product'][$i]['ParentID'] : NULL); // returns true

			// Create new Product object from Product class
			$me = new Product($productId, $productName, $productUrl, $productImageUrl, $parentId);
			
			// If ParentID is set, then set Product object -> isParent to false
			if (isset($array['Products']['Product'][$i]['ParentID'])) {
				$me->setToChild();
			}

			// Add it to localArray using $me->id as identifier
			$localArray[$me->id] = $me;

			// Go through localArray and look for parent
			foreach($localArray as $k) {
				// echo $k->id;

				if ($k->id == $parentId) {
					$k->setChildren($me);

					// Delete it from localArray because we already set it to parent
					unset($localArray[$me->id]);
				}

			}
		}

	}
?>

<div style="float:left;">

<?php

// Go through localArray and look for parent
foreach($localArray as $k) {
	echo '<div style="border:1px solid #000;float: left; width: 250px; height: 400px">';

	echo '<div style="border:1px solid red;">';
	echo  $k->name. ' - ' . $k->id;
	echo '<img src="' . $k->imageUrl . '" height="120"/>';
	echo '</div>';

	echo '<div style="clear:both"></div>';

	foreach($k->children as $c) {
		echo '<div style="border:1px solid blue;float: left; width: 50px;">';
		echo $c->id;
		echo '<img src="' . $c->imageUrl . '" height="50"/>';
		echo '</div>';
	}

	echo '</div>';
}


?>



</div>

</body>
</html>