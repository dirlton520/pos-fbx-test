
<?php 

require './dbconnection/env.php';
require './common_functions.php';
require './header.php';

$queryParentCatalog = "SELECT `pa`.`id`, `pa`.`name`, `pa`.`bitrix_property_id`, `pa`.`bitrix_attribute_code` FROM `phppos_attributes` AS `pa` LEFT JOIN `phppos_item_attributes` as `pia` ON `pia`.attribute_id = `pa`.id WHERE `pa`.`id`";


$result = $connect->query($queryParentCatalog);

$excludeProperty = ['property896', 'property895', 'property299'];
$excludeProperty = ['property896', 'property895'];

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

$allBitrixParentCatalogAttributes = json_decode(getBitrixParentCatalogAttributesList(24, 1), true)['result']['product'];

foreach ($allBitrixParentCatalogAttributes as $key => $propertyParent) {
	if ($propertyParent['type'] == 'productproperty' && $propertyParent['propertyType'] == 'L' && !in_array($key, $excludeProperty)) {
		$allBitrixAttributes[] = $propertyParent['name'];
		$posAttrId = checkNameExistsInPOS($propertyParent['name'], $allPOSAttributes);
		if ($posAttrId) {
			$readyToExportDataRenderArr[$key] = $propertyParent;
			$readyToExportDataRenderArr[$key]['pos_attr_id'] = $posAttrId;
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
// echo "<pre>";
// echo "<br>=== allPOSAttributes <br>";
// print_r($allPOSAttributes);
// echo "<br>=== allBitrixAttributes <br>";
// print_r($allBitrixAttributes);
// // echo "<br>=== readyToExportDataRenderArr <br>";
// // print_r($readyToExportDataRenderArr);
// echo "<br>=== manualMappignExportDataRenderArr <br>";
// print_r($finalManualMappingArr); 
// echo "<br>=== createNewExportDataRenderArr <br>";
// print_r($createNewExportDataRenderArr);
// echo "</pre>";
?>
			<!-- Main Content -->
            <div id="content">

                <!-- Begin Page Content -->
                <div class="container-fluid">
					<form method="POST" action="save-step-1.php">
						<!-- Page Heading -->
						<h1 class="h3 mb-2 text-gray-800">POS Attributes </h1>

						<?php if (!empty($readyToExportDataRenderArr)) { ?>
							<!-- Ready to Import -->
							<div class="card shadow mb-4">
								<div class="card-header py-3">
									<h6 class="m-0 font-weight-bold text-primary">Ready To Import</h6>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-bordered " width="100%" cellspacing="0">
											<tr class="bg-primary text-light">
												<th>Bitrix Property</th>
												<th>POS Attributes</th>
											</tr>
											<?php 
												foreach($readyToExportDataRenderArr as $propKey => $readyToExportDataRender) {
											?>
												<tr>
													<input type="hidden" name="ready_to_import[]" value="<?= $propKey ?>_<?= $readyToExportDataRender['pos_attr_id'] ?>" />
													
													<td>
														<?= $allBitrixParentCatalogAttributes[$propKey]['name'] ?>
													</td>
													<td>
														<?= $allPOSAttributes[$readyToExportDataRender['pos_attr_id']] ?>
													</td>
												</tr>
											<?php } ?>
										</table>
									</div>
								</div>
							</div>
						<?php } ?>
						<!-- Manual Mapping -->
						<div class="card shadow mb-4">
							<div class="card-header py-3">
								<h6 class="m-0 font-weight-bold text-primary">Manual Mapping</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table_attributes" width="100%" cellspacing="0">
										<tr class="bg-primary text-light">
											<th width="50%">Bitrix Property</th>
											<th>POS Attributes</th>

										</tr>

										<?php if (!empty($manualMappignExportDataRenderArr)) { 
											foreach ($manualMappignExportDataRenderArr as $propKey => $manualMappignExportDataRender)
											?>
											<tr>
											<input class="manual_mapping" type="hidden" name="manual_mapping[]" value="<?= $propKey ?>_0" />
												<td>
													<?= $allBitrixParentCatalogAttributes[$propKey]['name'] ?>
												</td>
												<td>
														<?php 
															krsort($manualMappignExportDataRender);
														?>
														<select onchange="updateSelectedMapping(this)" class="form-control select2">
															<option>--Please Select Attribute--</option>
															<?php
															foreach($manualMappignExportDataRender as $percent => $attributeData) { 
																foreach ($attributeData as $atd) {
															?>
																	<option value="<?= $atd['pos_attr_id'] ?>"><?= $atd['name'] ?> (<?= $percent ?>% similar match)</option>
															<?php	} ?>
															<?php } ?>
														</select>
												</td>
											</tr>
										<?php } ?>
									</table>
								</div>
							</div>
						</div>
						<!-- create New -->
						<div class="card shadow mb-4">
							<div class="card-header py-3">
								<h6 class="m-0 font-weight-bold text-primary">Create New</h6>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered" style="border:1px solid #e3e6f0" width="100%" cellspacing="0">
										<?php 
										$count = 0;
										foreach($createNewExportDataRenderArr as $createNewExportDataRender) { 
											if ($count == 0) {
												echo "<tr>";		
											}
										?>
											<td>
												<div class="form-check">
													<input class="form-check-input" name="create_new_attr[]" type="checkbox" value="<?= $createNewExportDataRender['pos_attr_id'] ?>" >
													<label class="form-check-label" for="defaultCheck1">
														<?= $createNewExportDataRender['name'] ?>
													</label>
												</div>
											</td>
										<?php
											$count += 1; 
											if ($count == 2) {
												echo "</tr>";		
												$count = 0;
											}
										} 
										?>
									</table>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-body">
								<button class="btn btn-primary float-right" type="submit">Export</button>
							</div>
						</div>
					</form>
                </div>
                <!-- /.container-fluid -->
            </div>
			<script>
				function updateSelectedMapping(e) {
					var old_mapping_data = $(e).parent().parent().find('.manual_mapping').val().split("_");
					var selectedAttribute = $(e).val();
					$(e).parent().parent().find('.manual_mapping').val(old_mapping_data[0]+"_"+selectedAttribute);
				}
			</script> 
            <!-- End of Main Content -->
<?php
require './footer.php';
?>