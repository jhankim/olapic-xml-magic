<?php

header('Access-Control-Allow-Origin: *');

ini_set('memory_limit', '-1');

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

require_once('classes/product.class.php');
require_once('classes/status.class.php');

$status = new Status('1','default');

global $response;

$response = array(
    "metadata" => array(),
    "data" => array(),
);

if (!empty($_GET["url"])) {

	$input = htmlspecialchars($_GET["url"]);

	validateXsd($input, 'url', $status);

	echo json_encode($response,JSON_PRETTY_PRINT);


} elseif (isset($_FILES['file'])) {

	$input = $_FILES['file']['tmp_name'];

	validateXsd($input, 'file', $status);

	echo json_encode($response,JSON_PRETTY_PRINT);

} elseif ( $_GET["url"] == '' ) {
	$status->setCode(5);
	echo json_encode($response,JSON_PRETTY_PRINT);
}

function validateXsd($inputFile, $type, $statusObj) {

	// print_r(file_get_contents($inputFile));die();

	global $response;

	$xmlfile = $inputFile;
	$xsdfile = 'http://photorank.me/olapicProductFeedV1_0.xsd';

	if ( $type == "url" ) {

		if(validateUrl($xmlfile) > 400 || validateUrl($xmlfile) == 0) {
			$statusObj->setCode(5);
			$convObj = (array)$statusObj;
			$response['metadata'] = $convObj;
			return false;
		}

	}
	libxml_use_internal_errors(true);
 
	$feed = new DOMDocument();
	$feed->preserveWhitespace = false;
	$result = $feed->load($inputFile);

	if ( !$result ) {
		$statusObj->setCode(3);
		$errors = libxml_get_errors();
		$response['data'] = (array)$errors;
	} elseif ( !@($feed->schemaValidate($xsdfile)) ) {
		$statusObj->setCode(2);
		$errors = libxml_get_errors();
		$response['data'] = (array)$errors;
	}

	if( $result === TRUE && @($feed->schemaValidate($xsdfile)) ) {
		$statusObj->setCode(1);

		$response['data'] = (array)generateStructure($xmlfile);
	}

	$convObj = (array)$statusObj;
	$response['metadata'] = $convObj;

}

function generateStructure($input) {
	
	$xml = simplexml_load_file($input,'SimpleXMLElement', LIBXML_NOCDATA);
	$json = json_encode($xml,JSON_PRETTY_PRINT);
	$array = json_decode($json,TRUE);
	$localArray = array();

	$max = sizeof($array['Products']['Product']);

	for ($i = 0; $i < $max ; $i++) {

		$productId = $array['Products']['Product'][$i]['ProductUniqueID'];
		$productName = $array['Products']['Product'][$i]['Name'];
		$productUrl = $array['Products']['Product'][$i]['ProductUrl'];
		$productImageUrl = $array['Products']['Product'][$i]['ImageUrl'];
		$parentId = (isset($array['Products']['Product'][$i]['ParentID']) ? $array['Products']['Product'][$i]['ParentID'] : NULL); 
		$color = (isset($array['Products']['Product'][$i]['Color']) ? $array['Products']['Product'][$i]['Color'] : NULL); 

		// Create new Product object from Product class
		$me = new Product($productId, $productName, $productUrl, $productImageUrl, $parentId, $color);

		// If ParentID is set, then set Product object -> isParent to false
		if (isset($me->parentId)) {
			// echo $me->id . ' is child ' . PHP_EOL;
			$me->setToChild();
		}

		// Add it to localArray using $me->id as identifier
		$localArray[$me->id] = $me;

	}

	// Go through localArray and look for child and move it
	foreach($localArray as $k) {

		if ( $k->isParent == false ) {

			$tempId = $k->parentId;

			foreach($localArray as $c) {

				if ($tempId == $c->id) {
					$c->setChildren($k);

					// Delete it from localArray because we already set it to parent
					unset($localArray[$k->id]);
				}
			}
		}
	}

	return $localArray;
}

function validateUrl($url) {
	$handle = curl_init($url);
	curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

	/* Get the HTML or whatever is linked in $url. */
	$curlResponse = curl_exec($handle);

	/* Check for 404 (file not found). */
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

	curl_close($handle);

	return $httpCode;
}


?>