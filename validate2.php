<?php

opcache_reset();
header('Access-Control-Allow-Origin: *');  

ini_set('memory_limit', '-1');

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

require_once('classes/product.class.php');
require_once('classes/status.class.php');

$status = new Status('1','default');

if (!empty($_GET["url"])) {

	$input = htmlspecialchars($_GET["url"]);

	echo 'Got URL: ' . $input . ' ' . PHP_EOL .PHP_EOL ;

	$ch = curl_init($input);

	$headers = array();
	$headers[] = 'Accept: application/xml';
	$headers[] = 'Content-Type: application/xml';

	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	curl_exec($ch);

	print_r(curl_getinfo($ch));

	$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// $retcode >= 400 -> not found, $retcode = 200, found.
	curl_close($ch);

	if ( $retcode >= 400 || $retcode == 0 ) {
		$status->setId('5');
		$status->setMessage('Error. File not found, please provide valid url');
	}

	// echo json_encode((array)$status);

} elseif (isset($_FILES['file'])) {

	echo 'file uploaded';

} else {

	header('HTTP/ 433 Reason Phrase As You Wish');
	$status['error'] = 1;
	$status['message'] = 'Error. Please provide a URL.';
	echo json_encode($status);

}

function validateXsd($inputFile, $bool) {
	$xmlfile = htmlspecialchars($_GET["url"]);
	$xsdfile = 'http://photorank.me/olapicProductFeedV1_0.xsd';

	libxml_use_internal_errors(true);
 
	$feed = new DOMDocument();
	$feed->preserveWhitespace = false;
	$result = $feed->load($xmlfile);


	if($result === TRUE) {
		$status['wellformed'] = true;
	} else {
		$status['error'] = 1;
		$status['wellformed'] = false;
	}

	if(@($feed->schemaValidate($xsdfile))) {
		$status['valid'] = true;
	} else {
		
		$status['error'] = 1;
		$status['valid'] = false;
		// var_dump the error messages
		$errors = libxml_get_errors();

		$status['validationErrorCount'] = $errors;
	}

	

	if (@($feed->schemaValidate($xsdfile)) && $result === TRUE) {
		$status['products'] = generateStructure($xmlfile);
	}

	echo json_encode($status);
}

function generateStructure($input) {
	
	$xml = simplexml_load_file($input,'SimpleXMLElement', LIBXML_NOCDATA);
	$json = json_encode($xml,JSON_PRETTY_PRINT);
	$array = json_decode($json,TRUE);
	$localArray = array();

	$max = sizeof($array['Products']['Product']);

	for ($i = 0; $i < 10 ; $i++) {
		// print_r($array['Products']['Product'][$i]);

		$productId = $array['Products']['Product'][$i]['ProductUniqueID'];
		$productName = $array['Products']['Product'][$i]['Name'];
		$productUrl = $array['Products']['Product'][$i]['ProductUrl'];
		$productImageUrl = $array['Products']['Product'][$i]['ImageUrl'];
		$parentId = (isset($array['Products']['Product'][$i]['ParentID']) ? $array['Products']['Product'][$i]['ParentID'] : NULL); 
		$color = (isset($array['Products']['Product'][$i]['Color']) ? $array['Products']['Product'][$i]['Color'] : NULL); 

		// Create new Product object from Product class
		$me = new Product($productId, $productName, $productUrl, $productImageUrl, $parentId, $color);
		
		// If ParentID is set, then set Product object -> isParent to false
		if (isset($array['Products']['Product'][$i]['ParentID'])) {
			$me->setToChild();
		}

		// Add it to localArray using $me->id as identifier
		$localArray[$me->id] = $me;

	}

	// Go through localArray and look for child and move it
	foreach($localArray as $k) {

		echo $k->id . PHP_EOL;

		if ( $k->isParent == false ) {

			$tempId = $k->parentId;

			echo 'starting array search since ' . $k->id . ' is child' . PHP_EOL;

			foreach($localArray as $c) {

				print_r($c);

				if ($tempId == $c->id) {
					echo 'found parent' . PHP_EOL;
				}
				// echo $c->id . PHP_EOL;
				// // echo 'from :' . $k->id. PHP_EOL;

				// if ($c->id = $k->parentId) {
				// 	echo 'parent found' . PHP_EOL;
				// }

				// 	echo 'For child: ' . $k->id;
				// 	// $c->setChildren($k);

				// 	// Delete it from localArray because we already set it to parent
				// 	// unset($localArray[$k->id]);
				// }

			}

		}

		echo PHP_EOL;
		echo PHP_EOL;
	}

	print_r($localArray); die();

	return $localArray;
}

function validateUrl($url) {


	// return $status;
}


?>