<?php

require '../../dbconnection/env.php';
require '../../common_functions.php';

echo "<pre>";

$queryChildCatalog = "SELECT `pa`.`id`, `pa`.`name`, `pa`.`bitrix_property_id`, `pa`.`bitrix_attribute_code` FROM `phppos_attributes` AS `pa` LEFT JOIN `phppos_item_attributes` as `pia` ON `pia`.attribute_id = `pa`.id WHERE `pa`.`id` IN (SELECT `pav`.`attribute_id` FROM `phppos_attribute_values` AS `pav` LEFT JOIN `phppos_item_variation_attribute_values` as `pivav` ON `pivav`.`attribute_value_id` = `pav`.`id` GROUP BY `pav`.`attribute_id`) GROUP BY `pa`.`id`;";


$result = $connect->query($queryChildCatalog);

$allPOSAttributes = [];
$allBitrixAttributes = [];
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
	$allPOSAttributes[$row['id']] = $row['name'];
  }
}

$readyToExportDataRenderArr = [];
$manualMappignExportDataRenderArr = [];
$createNewExportDataRenderArr = [];
$alreadyMappedArr = [];

$allBitrixParentCatalogAttributes = json_decode(getBitrixParentCatalogAttributesList(26, 4), true)['result']['product'];

foreach ($allBitrixParentCatalogAttributes as $key => $propertyParent) {
	if ($propertyParent['type'] == 'productproperty') {
		$allBitrixAttributes[] = $propertyParent['name'];
		if (checkNameExistsInPOS($propertyParent['name'], $allPOSAttributes)) {
			$readyToExportDataRenderArr[$key] = $propertyParent;
			$alreadyMappedArr[] = $propertyParent['name'];
		}
		else { 
			$comparisonsPercentageArray = isManualMappingRequired($propertyParent['name'], $allPOSAttributes, $alreadyMappedArr);
						
			if ($comparisonsPercentageArray) {				
				$manualMappignExportDataRenderArr[$key] = $comparisonsPercentageArray;
			}
		}
	}
}

$finalManualMappingArr = [];
foreach ($manualMappignExportDataRenderArr as $mkey => $manualMappingRow) {
	foreach ($manualMappingRow as $key => $rowPercentage) {
		foreach ($rowPercentage as $fkey => $attrName) {
			if (!in_array($attrName, $alreadyMappedArr)) {
				$finalManualMappingArr[$mkey][$key][$fkey] = $attrName;
				if (!in_array($attrName, $createNewExportDataRenderArr))
				$createNewExportDataRenderArr[] = $attrName;
			}
		}
	}
}

echo "<br>=== allBitrixAttributes <br>";
print_r($allBitrixAttributes);
echo "<br>=== readyToExportDataRenderArr <br>";
print_r($readyToExportDataRenderArr);
echo "<br>=== manualMappignExportDataRenderArr <br>";
print_r($finalManualMappingArr); 
echo "<br>=== createNewExportDataRenderArr <br>";
print_r($createNewExportDataRenderArr); die;

?>