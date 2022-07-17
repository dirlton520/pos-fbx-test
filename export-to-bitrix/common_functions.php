<?php

	function getBitrixParentCatalogAttributesList($catalogId, $productType) {
		$response = file_get_contents("http://localhost/ADA/NewPhpPOS/sqscalls/bitrix-config-request.php");
		$config = json_decode(base64_decode($response), true);		
		$inboundUrl = $config['bitrix']['inbound_url'];

		$inboundUrl .= "catalog.product.getFieldsByFilter.json?select[0]=id&select[1]=*&filter[iblockId]=".$catalogId."&filter[productType]=".$productType;

		return callInboundAPI($inboundUrl);
	}

	function isManualMappingRequired($compareString, $compareInArray, $alreadyMappedArr) {

		$similarOptions = [];
		foreach ($compareInArray as $name) {
			if (!empty($alreadyMappedArr) && in_array($name, $alreadyMappedArr)) {
				continue;
			}
			similar_text($compareString, $name, $percent);
			$result = array_keys($compareInArray, $name);
			if (!empty($result)) {
				$result = $result[0];
			}
			$similarOptions[round($percent)][] = ['name' => $name, 'pos_attr_id' => $result];
		}
		return $similarOptions;
	}

	function checkNameExistsInPOS($compareString, $compareInArray) {
		$trimBitrix = trim($compareString);
		$lowercaseBitrix = strtolower($trimBitrix);
		$removeAllSpacesBitrix = str_replace(" ","", $lowercaseBitrix);

		$trimPOS = array_map('trim', $compareInArray);
		$lowercasePOS = array_map('strtolower', $trimPOS);
		$removeAllSpacesPOS = array_map('remove_all_spaces', $lowercasePOS);

		if (in_array($trimBitrix, $trimPOS)) {	
			$result = array_keys($trimPOS, $trimBitrix);
			if (!empty($result)) {
				return $result[0];
			}
		}
		else if (in_array($lowercaseBitrix, $lowercasePOS)) {
			$result = array_keys($lowercasePOS, $lowercaseBitrix);
			if (!empty($result)) {
				return $result[0];
			}
		}
		else if (in_array($removeAllSpacesBitrix, $removeAllSpacesPOS)) {
			$result = array_keys($removeAllSpacesPOS, $removeAllSpacesBitrix);
			if (!empty($result)) {
				return $result[0];
			}
		}

		return false;
	}

	function remove_all_spaces($string) {
		return str_replace(" ","", $string);
	}

	function compareNameInArray($bitrixPropertyName, $posAttrName) {

		$comparisionCheck = false;

		$trimBitrix = trim($bitrixPropertyName);
		$trimPOS = trim($posAttrName);

		$lowercaseBitrix = strtolower($trimBitrix);
		$lowercasePOS = strtolower($trimPOS);

		$removeAllSpacesBitrix = str_replace(" ","", $lowercaseBitrix);
		$removeAllSpacesPOS = str_replace(" ","", $lowercasePOS);

		if ($bitrixPropertyName == $posAttrName || $trimBitrix == $trimPOS || $lowercaseBitrix == $lowercasePOS || $removeAllSpacesBitrix == $removeAllSpacesPOS) {
			$comparisionCheck = true;
		}
		return $comparisionCheck;
	}

	function callInboundAPI($inboundUrl) {
		return file_get_contents($inboundUrl);
	}	

?>