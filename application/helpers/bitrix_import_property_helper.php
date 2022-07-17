<?php 

function allBitrixCatalogKeys($key, $catalogId) {	
	$config = getBitrixConfig();

	if ($catalogId == $config['bitrix']['parent_catalog_iblockId']) {
		$catalogProductFields = $config['bitrix']['parent_catalog_static_mapped_fields'];
	}
	else {
		$catalogProductFields = $config['bitrix']['child_catalog_static_mapped_fields'];
	}

	if (isset($catalogProductFields[$key])) {
		return $catalogProductFields[$key];
	}
}

function getBitrixField($catalogId) {
	$config = getBitrixConfig();
	$CI = get_instance();	
	$productType = ($catalogId == $config['bitrix']['parent_catalog_iblockId'])?1:4;
	$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."catalog.product.getFieldsByFilter.json?select[0]=id&select[1]=*&filter[iblockId]=".$catalogId."&filter[productType]=".$productType);					
	$allProparties = $result = json_decode($jsonResult, true);
	if (isset($allProparties['result']['product'])) {
		return $allProparties['result']['product'];
	}
	else {
		return [];
	}
}

function savePOSFieldMappedWithBitrix($catalogId) {
	$config = getBitrixConfig();
	$CI = get_instance();	
	
	$productType = ($catalogId == $config['bitrix']['parent_catalog_iblockId'])?1:4;

	$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."catalog.product.getFieldsByFilter.json?select[0]=id&select[1]=*&filter[iblockId]=".$catalogId."&filter[productType]=".$productType);					
	$allProparties = $result = json_decode($jsonResult, true);
	$finalData = [];

	foreach ($allProparties['result']['product'] as $key => $prop) {
		$bitrixArray = ['catalog_id' => $catalogId,'bitrix_field_code'=>$key, 'name' => $prop['name'], 'type' => $prop['type'], 'userType' => (isset($prop['userType']))?$prop['userType']:0, 'values' => (!empty($prop['values']))?1:0, 'propertyType' => @$prop['propertyType']];


		$posArray = allBitrixCatalogKeys($key, $catalogId);		
		if (!empty($posArray)) {
			$finalData[$key] = array_merge($bitrixArray, $posArray);
		}
	} 

	$sellingPriceField = allBitrixCatalogKeys('selling_price', $catalogId);

	if (!empty($sellingPriceField)) {
		$bitrixArray = ['catalog_id' => $catalogId,'bitrix_field_code'=>'selling_price', 'name' => 'Base Price', 'type' => 'double', 'userType' => (isset($prop['userType']))?$prop['userType']:0, 'values' => 0, 'propertyType' => NULL];
		$finalData['selling_price'] = array_merge($bitrixArray, $sellingPriceField);		
	}

	foreach ($finalData as $field) {
		$CI->db->from('phppos_bitrix_field_mapping');
		$CI->db->where('bitrix_field_code', $field['bitrix_field_code']);
		$CI->db->where('catalog_id', $catalogId);		

		$query = $CI->db->get();
		if (!empty($query) && $query->num_rows()==0) {
			$CI->db->insert('phppos_bitrix_field_mapping', $field);
		}
	}
}

function getAllMappedFieldsFromDB($items) {
	$CI = get_instance();
	$config = getBitrixConfig();
	
	/** Save all field mapping data - start*/
	savePOSFieldMappedWithBitrix($items['iblockId']);
	/** Save all field mapping data - end */
	
	$allFields = $CI->BitrixFieldMapping->getList($items['iblockId']);
	$columns = [];
	if (!empty($allFields)) {
		foreach ($allFields as $field) {
			$columns[$field['pos_insert_after_item']][] = $field;
		}
	}

	return $columns;
}

function importAttributePropertyValue($item, $item_id, $bitrixFieldCode, $field, $allBitrixFieldsList) {
	$CI = get_instance();
	$config = getBitrixConfig();

	if (isset($item[$bitrixFieldCode])) {
		$attributeId = createPOSAttribute($field);
		if ($attributeId > 0) {
			$CI->load->model('Item_attribute');
			$CI->Item_attribute->save_item_attributes([$attributeId], $item_id, false);

			$allOptions = importAttributeValue($attributeId, $bitrixFieldCode, $allBitrixFieldsList, $item[$bitrixFieldCode]);
			$CI->load->model('Item_attribute_value');
			$attributeValues = $CI->Item_attribute_value->get_values_for_attribute($attributeId);
	
			if (!empty($attributeValues)) {
				$attrValueIds = [];
				$selectedValueId = NULL;
				
				foreach ($attributeValues->result_array() as $attrValue) {
					if ($field['propertyType'] == 'S') {
						$selectedValueId =  $item[$bitrixFieldCode]['valueId'];
					}
					else if ($field['propertyType'] == 'L') {
						$selectedValueId = $item[$bitrixFieldCode]['value'];
					}
					if ($attrValue['bitrix_property_value_id'] == $selectedValueId) {
						$attrValueIds[] = $attrValue['id'];
					}					
				}

				if (!empty($attrValueIds)) {
					$CI->Item_attribute_value->save_item_attribute_values($item_id, $attrValueIds);
				}

				return ['attribute_id' => $attributeId, 'attribute_selected_values' => $attrValueIds];
			}
		}		
	}
}

function createPOSAttribute($item) {
	$CI = get_instance();
	$bitrixFieldId = str_replace("property","",$item['bitrix_field_code']);
	$attributeData = ['name' => $item['pos_field_code'], 'bitrix_property_id' => $bitrixFieldId, 'bitrix_attribute_code' => $item['bitrix_field_code']];
	$CI->db->from('attributes');
	$CI->db->where('bitrix_property_id', $bitrixFieldId);		
	$query = $CI->db->get();		

	$id = NULL;
	if ($query->num_rows()==1) {
		$id = $query->row()->id;
		$CI->db->where('id', $id)->update('attributes', $attributeData);
	}
	else {
		if ($CI->db->insert('attributes', $attributeData)) {
			$id = $CI->db->insert_id();	
		}
	}
	return $id;
}

function importAttributeValue($attributeId, $bitrixFieldCode, $allBitrixFieldsList, $bitrixSelectedAttributeValue) {
	$bitrixFieldId = str_replace("property","",$bitrixFieldCode);
	$CI = get_instance();
	$allOptions = [];

	if (isset($allBitrixFieldsList[$bitrixFieldCode])) {	
		if ($allBitrixFieldsList[$bitrixFieldCode]['propertyType'] == 'L' && !empty($allBitrixFieldsList[$bitrixFieldCode]['values'])) {
			foreach ($allBitrixFieldsList[$bitrixFieldCode]['values'] as $value) {
				$attrValue['name'] = $value['value'];
				$attrValue['attribute_id'] = $attributeId;
				$attrValue['bitrix_property_id'] = $bitrixFieldId;
				$attrValue['bitrix_property_value_id'] = $value['id'];
				$attrValue['deleted '] = 0;
			
				$CI->db->from('attribute_values');
				$CI->db->where('bitrix_property_value_id', $value['id']);		
				$query = $CI->db->get();		
				
				if ($query->num_rows()==1) {
					$id = $query->row()->id;
					$CI->db->where('id', $id)->update('attribute_values', $attrValue);
				} else {
					$CI->db->insert('attribute_values', $attrValue);
					$id = $CI->db->insert_id();
				}					
				$allOptions[$value['id']] = $id;
			}
		}
		else if ($allBitrixFieldsList[$bitrixFieldCode]['propertyType'] == 'S') {
			$attrValue['name'] = $bitrixSelectedAttributeValue['value'];
			$attrValue['attribute_id'] = $attributeId;
			$attrValue['bitrix_property_id'] = $bitrixFieldId;
			$attrValue['bitrix_property_value_id'] = $bitrixSelectedAttributeValue['valueId'];
			$attrValue['deleted '] = 0;
		
			$CI->db->from('attribute_values');
			$CI->db->where('bitrix_property_value_id', $bitrixSelectedAttributeValue['valueId']);		
			$query = $CI->db->get();		
			if ($query->num_rows()==1) {
				$id = $query->row()->id;
				$CI->db->where('id', $id)->update('attribute_values', $attrValue);
			} else {
				$CI->db->insert('attribute_values', $attrValue);
				$id = $CI->db->insert_id();
			}
			$allOptions[$bitrixSelectedAttributeValue['valueId']] = $id;
		}
	}
	return $allOptions;
}

function importManufacturers($items, $item_id, $propertyId, $propertyType = 'L') {
	$CI = get_instance();
	$CI->load->model('Manufacturer');
	$manufacturerId = 0;
	if(isset($items[$propertyId])) {
		$config = getBitrixConfig();		
		$productType = ($items['iblockId'] == $config['bitrix']['parent_catalog_iblockId'])?1:4;	
		$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."catalog.product.getFieldsByFilter.json?select[0]=id&select[1]=*&filter[iblockId]=".$items['iblockId']."&filter[productType]=".$productType);					
		$allProparties = json_decode($jsonResult, true);

		if ($propertyType == 'L' && isset($allProparties['result']['product'][$propertyId]['values'][$items[$propertyId]['value']])) {
			$propertData = $allProparties['result']['product'][$propertyId]['values'][$items[$propertyId]['value']];
			$manufacturer = $propertData['value'];
			$bitrixManufacturerId = $propertData['id'];
		} else if ($propertyType == 'S'){
			$manufacturer = $items[$propertyId]['value'];
			$bitrixManufacturerId = $items[$propertyId]['valueId'];
		}

		if (isset($bitrixManufacturerId)) {
			$CI->db->from('phppos_manufacturers');
			$CI->db->where('bitrix_manufacturer_id', $bitrixManufacturerId);
			$result = $CI->db->get()->result_array();
			if (isset($result[0]['id'])) {
				$manufacturerId = addManufacturers($manufacturer, $bitrixManufacturerId,$result[0]['id']);
			}
			else {
				$manufacturerId = addManufacturers($manufacturer, $bitrixManufacturerId);
			}
		}
	}

	if ($manufacturerId > 0 && $item_id > 0) {
		$CI->db->query("UPDATE `phppos_items` SET  `manufacturer_id` = '".$manufacturerId."'  WHERE `item_id` = '".$item_id."'");
	}
}

function importItemKitManufacturers($items, $item_id, $propertyId, $propertyType = 'L') {
	$CI = get_instance();
	$CI->load->model('Manufacturer');
	$manufacturerId = 0;
	if(isset($items[$propertyId])) {
		$config = getBitrixConfig();		
		$productType = ($items['iblockId'] == $config['bitrix']['parent_catalog_iblockId'])?1:4;	
		$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."catalog.product.getFieldsByFilter.json?select[0]=id&select[1]=*&filter[iblockId]=".$items['iblockId']."&filter[productType]=".$productType);					
		$allProparties = json_decode($jsonResult, true);

		if ($propertyType == 'L' && isset($allProparties['result']['product'][$propertyId]['values'][$items[$propertyId]['value']])) {
			$propertData = $allProparties['result']['product'][$propertyId]['values'][$items[$propertyId]['value']];
			$manufacturer = $propertData['value'];
			$bitrixManufacturerId = $propertData['id'];
		} else if ($propertyType == 'S'){
			$manufacturer = $items[$propertyId]['value'];
			$bitrixManufacturerId = $items[$propertyId]['valueId'];
		}

		if (isset($bitrixManufacturerId)) {
			$CI->db->from('phppos_manufacturers');
			$CI->db->where('bitrix_manufacturer_id', $bitrixManufacturerId);
			$result = $CI->db->get()->result_array();
			if (isset($result[0]['id'])) {
				$manufacturerId = addManufacturers($manufacturer, $bitrixManufacturerId,$result[0]['id']);
			}
			else {
				$manufacturerId = addManufacturers($manufacturer, $bitrixManufacturerId);
			}
		}
	}

	if ($manufacturerId > 0 && $item_id > 0) {
		$CI->db->query("UPDATE `phppos_item_kits` SET  `manufacturer_id` = '".$manufacturerId."'  WHERE `item_kit_id` = '".$item_id."'");
	}
}

function addManufacturers($manufacturer_name, $bitrixManufacturerId, $manufacturer_id = FALSE)
{
		$CI = get_instance();
		if ($manufacturer_id == FALSE)
		{
			if ($manufacturer_name)
			{
				if($CI->db->insert('manufacturers',array('name' => $manufacturer_name, 'bitrix_manufacturer_id' => $bitrixManufacturerId)))
				{
					return $CI->db->insert_id();
				}
			}			
			return FALSE;
		}
		else
		{
			$CI->db->where('id', $manufacturer_id);
			if ($CI->db->update('manufacturers',array('name' => $manufacturer_name, 'bitrix_manufacturer_id' => $bitrixManufacturerId)))
			{
				return $manufacturer_id;
			}
		}
		return FALSE;
}

function importSellingPrice($items, $propertyId) {
	if($propertyId == 'selling_price') {
		$productId = $items['id'];
		$CI = get_instance();
		$config = getBitrixConfig();
		$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."catalog.price.list.json?filter[productId]=".$productId."&select[0]=price");
		$result = json_decode($jsonResult, true);		 
		if (isset($result['result']['prices'][0])) {
			return $result['result']['prices'][0]['price'];
		}
		else {
			return 0;
		}
	}
	else {
		return 0;
	}
}

function importSuppliers($items, $propertyId) {
		/** Import Suppliers From Bitrix */
		if(isset($items[$propertyId])) {
			$companyId = $items[$propertyId]['value'];
			$CI = get_instance();
			$CI->load->model('supplier');
			$CI->db->select('company_name');
			$CI->db->from('phppos_suppliers');
			$CI->db->where('bitrix_company_id', $companyId);
			$exist =  $CI->db->get()->result();
			if(empty($exist)){
				$config = getBitrixConfig();
				$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."crm.company.get.json?ID=".$companyId);
				$companyList = json_decode($jsonResult, true);
				if (isset($companyList['result'])) {
					$supplierPersonFirstName = '';
					$supplierPersonLastName = '';
					$supplierPhone = '';
					$supplierEmail = '';
					$companyContact = callInboundAPI($config['bitrix']['inbound_url']."crm.company.contact.items.get.json?ID=".$companyList['result']['ID']);
					$companyContactDetails = json_decode($companyContact, true);
					if(!empty($companyContactDetails) ){
						$primaryKeyY = [];
						$primaryKeyN = [];
						foreach($companyContactDetails['result'] as $key){
							if($key['IS_PRIMARY'] == 'Y'){
								$primaryKeyY[] = $key['CONTACT_ID'];
							}
							else{
								$primaryKeyN[] = $key['CONTACT_ID'];
							}
						}
						if(!empty($primaryKeyY)){			
							$companyPersonContact = callInboundAPI($config['bitrix']['inbound_url']."crm.contact.get.json?ID=".$primaryKeyY[0]);
							$companyPersonDetails = json_decode($companyPersonContact, true);
							if(!empty($companyPersonDetails)){
								$supplierPersonFirstName = $companyPersonDetails['result']['NAME'];
								$supplierPersonLastName = $companyPersonDetails['result']['LAST_NAME'];
								$supplierPhone = (!empty($companyPersonDetails['result']['PHONE'][0]['VALUE']))?$companyPersonDetails['result']['PHONE'][0]['VALUE']:'';
								$supplierEmail = (!empty($companyPersonDetails['result']['EMAIL'][0]['VALUE']))?$companyPersonDetails['result']['EMAIL'][0]['VALUE']:'';								
							}
						}
						elseif(!empty($primaryKeyN)){
							$companyPersonContact = callInboundAPI($config['bitrix']['inbound_url']."crm.contact.get.json?ID=".$primaryKeyN[0]);
								$companyPersonDetails = json_decode($companyPersonContact, true);
								if(!empty($companyPersonDetails)){
									$supplierPersonFirstName = $companyPersonDetails['result']['NAME'];
									$supplierPersonLastName = $companyPersonDetails['result']['LAST_NAME'];
									$supplierPhone = (!empty($companyPersonDetails['result']['PHONE'][0]['VALUE']))?$companyPersonDetails['result']['PHONE'][0]['VALUE']:'';
									$supplierEmail = (!empty($companyPersonDetails['result']['EMAIL'][0]['VALUE']))?$companyPersonDetails['result']['EMAIL'][0]['VALUE']:'';
								}
						}					
					}
					$list = $companyList['result'];
					$person_data = array(
						'first_name'=>(!empty($supplierPersonFirstName))?$supplierPersonFirstName:'',
						'last_name'=>(!empty($supplierPersonLastName))?$supplierPersonLastName:'',
						'email'=>(!empty($list['EMAIL']['VALUE']))?$list['EMAIL']['VALUE']:$supplierEmail,
						'phone_number'=>(!empty($list['PHONE']['VALUE']))?$list['PHONE']['VALUE']:$supplierPhone,
						'address_1'=>(!empty($list['ADDRESS']))?$list['ADDRESS']:'',
						'address_2'=>(!empty($list['ADDRESS_2']))?$list['ADDRESS_2']:'',
						'city'=>(!empty($list['ADDRESS_CITY']))?$list['ADDRESS_CITY']:'',
						'state'=>(!empty($list['ADDRESS_PROVINCE']))?$list['ADDRESS_PROVINCE']:'',
						'zip'=>(!empty($list['ADDRESS_POSTAL_CODE']))?$list['ADDRESS_POSTAL_CODE']:'',
						'country'=>(!empty($list['ADDRESS_COUNTRY']))?$list['ADDRESS_COUNTRY']:'',
						'comments'=>(!empty($list['COMMENTS']))?$list['COMMENTS']:''
					);
					
					$supplier_data=array(
						'company_name'=>(!empty($list['TITLE']))?$list['TITLE']:null,
						'bitrix_company_id'=>(!empty($list['ID']))?$list['ID']:null,
						'account_number'=>null,
						'override_default_tax'=> 0,
						'tax_class_id'=> null,
						'internal_notes'=>null,
					);
					$supplier_id= '-1';
					$CI->supplier->save_supplier($person_data,$supplier_data,$supplier_id);					
				}
			}

			$CI->db->select('person_id');
			$CI->db->from('phppos_suppliers');
			$CI->db->where('bitrix_company_id', $companyId);
			$result = $CI->db->get()->result_array();
			if (isset($result[0]['person_id'])) {
				return (!empty($result[0]['person_id']))?$result[0]['person_id']:NULL;
			}
			else {
				return NULL;
			}
			 
		}
		else {
			return NULL;
		}
}

function getProductCategoryId($sectionParentId = NULL) {
	$categoryId = NULL;
	$CI = get_instance();
	if (!empty($sectionParentId)) {
		$phpposCategory = $CI->db->query("SELECT `id` FROM `phppos_categories` WHERE `bitrix_section_id` = '".$sectionParentId."' ")->row();
		if (!empty($phpposCategory)) {
			$categoryId = $phpposCategory->id;
		}
		else {
			$categoryId = importSectionForce($sectionParentId);
		}
	}
	return $categoryId;
}

function importSectionForce($section) {
	$config = getBitrixConfig();	
	$CI = get_instance();	
	if (!empty($section) && $section > 0) {
		$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."crm.productsection.get.json?ID=".$section);
		$result = json_decode($jsonResult, true);
		if(isset($result['result']) && !empty($result['result'])) {
			$item = $result['result'];
			if (addSectionsToBitrix($item, false)) {
				$phpposCategory = $CI->db->query("SELECT `id` FROM `phppos_categories` WHERE `bitrix_section_id` = '".$section."' ")->row();
				if (!empty($phpposCategory)) {
					return $phpposCategory->id;
				}
			}
		}
	}
	return 0;
}

function getProductWeightUnit($apiUrl = NULL) {
	if (!empty($apiUrl)) {
		$config = getBitrixConfig();
		$inboundUlr = $config['bitrix']['inbound_url'].$apiUrl;
		$jsonResult = callInboundAPI($inboundUlr);
		$result = json_decode($jsonResult, true);			
		$measure = @$result['result']['measure']['symbol'];
		$defaultUnits = ['lb','oz','kg','g','l','ml'];			
		if (!empty($measure) && in_array($measure, $defaultUnits)) {
			return $measure;
		}
		else {
			return false;
		}
	}
}

function importProductImage($item, $item_id, $bitrixFieldCode, $field, $variationId = 0) {
	$CI =  get_instance();
	$config = getBitrixConfig();
	if (!empty($bitrixFieldCode)) {
		$fieldId = (isset($item[$field['bitrix_field_code']]['id']) && !empty($item[$field['bitrix_field_code']]['id']))? $item[$field['bitrix_field_code']]['id']: 0;
 
		if (!empty($fieldId) && $fieldId > 0) {
			$downloadUrl = $config['bitrix']['inbound_url']."catalog.product.download?fields[fieldName]=".$bitrixFieldCode."&fields[fileId]=".$fieldId."&fields[productId]=".$item['id'];

			$fileData = $CI->db->query("SELECT `file_id` FROM `phppos_app_files`  WHERE `bitrix_file_id` = '".$fieldId."'")->result_array();

			$fileId = 0;
			if (empty($fileData)) {
				$fileId = uploadFileToPath($downloadUrl, $fieldId);
			}
			else {
				$fileId = $fileData[0]['file_id'];
			}

			$imageExists = $CI->db->query("SELECT `id` FROM `phppos_item_images` WHERE `item_kit_id` = '".$item_id."' AND `image_id` = '".$fileId."'");

			if ($imageExists) {
				$imageExists = $imageExists->result_array();
			}
 
			$CI->load->model('Item');

			if ($fileId && empty($imageExists)) {								
				$CI->Item->add_image($item_id, $fileId);
			}
			
			if ($fileId && $field['pos_field_code'] == 'main_image_id') {
				$CI->Item->set_main_image($item_id, $fileId);
			}			

			if ($fileId && $variationId > 0) {
				$CI->db->query("UPDATE `phppos_item_images` SET  `item_variation_id` = '".$variationId."'  WHERE `image_id` = '".$fileId."'");						
			}
			
		}
	}
}

function importItemKitProductImage($item, $item_id, $bitrixFieldCode, $field, $bundleId = 0) {
	$CI =  get_instance();
	$config = getBitrixConfig();
	if (!empty($bitrixFieldCode)) {
		$fieldId = (isset($item[$field['bitrix_field_code']]['id']) && !empty($item[$field['bitrix_field_code']]['id']))? $item[$field['bitrix_field_code']]['id']: 0;
 
		if (!empty($fieldId) && $fieldId > 0) {
			$downloadUrl = $config['bitrix']['inbound_url']."catalog.product.download?fields[fieldName]=".$bitrixFieldCode."&fields[fileId]=".$fieldId."&fields[productId]=".$item['id'];

			$fileData = $CI->db->query("SELECT `file_id` FROM `phppos_app_files`  WHERE `bitrix_file_id` = '".$fieldId."'")->result_array();

			$fileId = 0;
			if (empty($fileData)) {
				$fileId = uploadFileToPath($downloadUrl, $fieldId);
			}
			else {
				$fileId = $fileData[0]['file_id'];
			}

			$imageExists = $CI->db->query("SELECT `id` FROM `phppos_item_kit_images` WHERE `item_kit_id` = '".$item_id."' AND `image_id` = '".$fileId."'")->result_array();

			$CI->load->model('Item_kit');

			if ($fileId && empty($imageExists)) {								
				$CI->Item_kit->add_image($item_id, $fileId);
			}
			
			if ($fileId && $field['pos_field_code'] == 'main_image_id') {
				$CI->Item_kit->set_main_image($item_id, $fileId);
			}			
		}
	}
}

function uploadFileToPath($downloadUrl, $bitrixfielId = 0) {
	$CI =  get_instance();
	$arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
    ); 

	$data = file_get_contents($downloadUrl, true, stream_context_create($arrContextOptions));

	$filename = '';
	
	foreach ($http_response_header as $header) {
		if (preg_match('/Content-Disposition:.*?filename="(.+?)"/', $header, $matches)) {
			$filename = $matches[1];
			break;
		}
		if (preg_match('/Content-Disposition:.*?filename=([^; ]+)/', $header, $matches)) {
			$filename = rawurldecode($matches[1]);
			break;
		}
	}

	$CI->load->model('Appfile');
	$fileId = $CI->Appfile->save($filename, $data);

	if ($fileId) {
		$CI->db->query("UPDATE `phppos_app_files` SET  `bitrix_file_id` = '".$bitrixfielId."'  WHERE `file_id` = '".$fileId."'");	
	}

	return $fileId;
}

function importTags($items, $item_id, $bitrixFieldId = 0){	
	if ($bitrixFieldId) {
		if(isset($items[$bitrixFieldId])){
			$CI =  get_instance();
			if ($item_id > 0) {
				$bitrixTagId = $items[$bitrixFieldId]['valueId'];
				$bitrixTagValue = $items[$bitrixFieldId]['value'];
				$CT = get_instance();
				$CT->load->model('Tag');
				$saveTags = $CT->Tag->save_tags_for_item($item_id, $bitrixTagValue);
				if($saveTags){
					return true;
				}
				else{
					return false;
				}
			}
		}
	}
}

function importItemKitTags($items, $item_id, $bitrixFieldId = 0){	
	if ($bitrixFieldId) {
		if(isset($items[$bitrixFieldId])){
			$CI =  get_instance();
			if ($item_id > 0) {
				$bitrixTagId = $items[$bitrixFieldId]['valueId'];
				$bitrixTagValue = $items[$bitrixFieldId]['value'];
				$CT = get_instance();
				$CT->load->model('Tag');
				$saveTags = $CT->Tag->save_tags_for_item_kit($item_id, $bitrixTagValue);
				if($saveTags){
					return true;
				}
				else{
					return false;
				}
			}
		}
	}
}

function checkProductType($items, $propertyId = 'property92') {
	$config = getBitrixConfig();	

	$productData['type'] = ($items['bundle'] == 'Y')?'bundle':'simple'; 

	if ($productData['type'] == 'bundle') {
		/* 
		||--- This code needs to be implemented with bundle APIs response correction by Bitrix ---||
		$ids = [4870,4858,689];
		foreach ($ids as $id) {
			$inboundUlr = $config['bitrix']['inbound_url']."catalog.product.list.json?select[0]=id&select[1]=iblockId&select[2]=*&filter[iblockId]=".$config['bitrix']['parent_catalog_iblockId']."&filter[id]=".$id;
			$jsonResult = callInboundAPI($inboundUlr);
			$hasChildProducts = json_decode($jsonResult, true);			
			if (isset($hasChildProducts['result']['products'][0])) {
				$productData['child_products'][] = $hasChildProducts['result']['products'][0];
			}
		}
		*/
		$productData['child_products'] = [];
	}
	else {
		$inboundUlr = $config['bitrix']['inbound_url']."catalog.product.list.json?select[0]=id&select[1]=iblockId&select[2]=*&filter[iblockId]=".$config['bitrix']['child_catalog_iblockId']."&filter[".$propertyId."][value]=".$items['id'];

		$jsonResult = callInboundAPI($inboundUlr);
		$hasChildProducts = json_decode($jsonResult, true);

		if (isset($hasChildProducts['result']['products']) && count($hasChildProducts['result']['products']) > 0) {
			$productData['type'] = 'item_with_sku';
			$productData['child_products'] = $hasChildProducts['result']['products'];
		}
		else {
			$productData['type'] = 'simple';
		}
	}

	return $productData;
}

?>