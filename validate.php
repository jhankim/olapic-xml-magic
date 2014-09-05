<?php

opcache_reset();

ini_set('memory_limit', '-1');

require_once('classes/product.class.php');

$status = array();

if (!empty($_GET["url"])) {

	$xmlfile = htmlspecialchars($_GET["url"]);
	$xsdfile = 'http://photorank.me/olapicProductFeedV1_0.xsd';

	$ch = curl_init($xmlfile);

	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_exec($ch);
	$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// $retcode >= 400 -> not found, $retcode = 200, found.
	curl_close($ch);

	$status['extra'] = $retcode;

	if ( $retcode >= 400 || $retcode == 0 ) {
		$status['error'] = 1;
		$status['message'] = 'Error. File not found, please provide valid url';
	}

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

		// foreach($errors as $error) {
		// 	$errors[''][] = ("Error: %sLine: %s, column: %s, level: %s, code: %s",
		// 		$error->message,
		// 		$error->line,
		// 		$error->column,
		// 		$error->level,
		// 		$error->code
		// 	);
		// };
	}

	function generateStructure($input) {
		
		$xml = simplexml_load_file($input,'SimpleXMLElement', LIBXML_NOCDATA);
		$json = json_encode($xml,JSON_PRETTY_PRINT);
		$array = json_decode($json,TRUE);
		$localArray = array();

		$max = sizeof($array['Products']['Product']);

		for ($i = 0; $i < 100 ; $i++) {
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

		return $localArray;
	}

	if (@($feed->schemaValidate($xsdfile)) && $result === TRUE) {
		$status['products'] = generateStructure($xmlfile);
	}

	echo json_encode($status);

} else {

	$status['error'] = 1;
	$status['message'] = 'Error. Please provide a URL.';
	echo json_encode($status);

}

?>