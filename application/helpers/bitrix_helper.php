<?php
	function addItemsToPOS($event, $item) {
		$config = getBitrixConfig();
		$CI = get_instance();
		$CI->load->model('BitrixSubscription');
		$actionTypeSections = $config['bitrix']['sections']['events'];
		$actionTypeItems = $config['bitrix']['products']['events'];
		$addChildProducts = $config['bitrix']['subscribe_child_sections'];
		try {
			if (in_array($event, $actionTypeSections)) {
				if (checkIsSubscribed(1, $item['SECTION_ID'])) {					
					if (!checkIsSubscribed(1, $item['ID'])) {
						$data = ['type' => 1, 'value' => $item['ID']];
						$CI->BitrixSubscription->save($data);
					}
					addSectionsToBitrix($item, $addChildProducts);
				}
			}
			else if (in_array($event, $actionTypeItems)) {
				if (checkIsSubscribed(1, $item['SECTION_ID']) || checkIsSubscribed(2, $item['ID'])) {
					if ($event == 'ONCRMPRODUCTDELETE') {
						unsubscribeProductOnly(2, $id);
					}
					else {
						$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."catalog.product.list.json?select[0]=id&select[1]=iblockId&select[2]=*&filter[iblockId]=".$config['bitrix']['parent_catalog_iblockId']."&filter[id]=".$item['ID']);
						$result = json_decode($jsonResult, true);
						if(isset($result['result']['products']) && !empty($result['result']['products'])) {
							if (!empty($result['result']['products']) && count($result['result']['products']) > 0) {
								addProductsToBitrix($result['result']['products'][0]);
							}
						}
					}
				}
			}
		}
		catch (\Exception $e) {
			return false;
		}
		return true;
	}

	function checkIsSubscribed($type, $id) {
		$CI = get_instance();
		$subscribed = $CI->db->query("SELECT `id` FROM `phppos_bitrix_subscription` WHERE `type` = '".$type."' AND `value` = '".$id."'")->result_array();
		if (count($subscribed) > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	function unsubscribeProductOnly($type, $id) {
		$CI = get_instance();
		if ($CI->db->query("DELETE FROM `phppos_bitrix_subscription` WHERE `type` = '".$type."' AND `value` = '".$id."'")) {
			return true;
		}
		else {
			return false;
		}
		
	}	

	function addProductsToBitrix($items) {
		$items = (isset($items[0]))?$items[0]:$items;
		$CI = get_instance();
		$productIsSubscribed = true;
		$sectionAlreadySubscribed = $CI->db->query("SELECT `id` FROM `phppos_bitrix_subscription` WHERE `type` = '2' AND `value` = '".$items['id']."'")->result_array();
		
		if (count($sectionAlreadySubscribed) == 0) {
			$data = ['type' => 2, 'value' => $items['id']];
			$CI->load->model('BitrixSubscription');
			if (!$CI->BitrixSubscription->save($data)) {
				$productIsSubscribed = false;
			}
		}

		if ($productIsSubscribed) {

			/** Check Product Type - Start */
			$productTypeData = checkProductType($items);

			if ($productTypeData['type'] == 'simple') {
				addSimpleProduct($items);
			}
			else if($productTypeData['type'] == 'bundle') {
				addBundleProduct($items, $productTypeData['child_products']);
			}
			else if($productTypeData['type'] == 'item_with_sku') {
				addItemWithSku($items, $productTypeData['child_products']);
			}
			
			/** Check Product Type - Start */
		} else {
			return false;
		}
	}

	function addBundleProduct($items, $childProducts) {
		$itemKitId = addItemKitProduct($items);
		if ($itemKitId > 0 && !empty($childProducts)) {
			$itemKitItemData = [];
			foreach ($childProducts as $key => $childItem) {
				$itemKitItemId = addSimpleProduct($childItem);
				$itemKitItemData[$key]['item_kit_id'] = $itemKitItemId;
				$itemKitItemData[$key]['item_id'] = $itemKitId;
				$itemKitItemData[$key]['item_variation_id'] = NULL;
				$itemKitItemData[$key]['quantity'] = 1;
			}

			if (!empty($itemKitItemData)) {
				$CI = get_instance();
				$CI->load->model('Item_kit_items');
				$CI->Item_kit_items->save($itemKitItemData, $itemKitId);
			}
		}
	}

	function addItemKitProduct($items) {
		$CI = get_instance();
		$allBitrixFieldsList = getBitrixField($items['iblockId']);
		$CI->load->model('Item_kit');
		$itemExists = $CI->db->query("SELECT * FROM `phppos_item_kits` WHERE `product_id` = '".$items['id']."'")->result_array();

		$item_id = -1;
		$posItem = [];
		if (count($itemExists)) {
			$posItem = $itemExists[0];
			$item_id = $posItem['item_kit_id'];
		}

		/* Default Array Of POS Fields - Start */

		$insertItem = [
			"item_kit_number" => $items['id'],
			'product_id' => $items['id'],
			'name' => $items['name'],
			'category_id' => getProductCategoryId($items['iblockSectionId']),
			'info_popup'=> NULL,
			'manufacturer_id'=>NULL,
			'description' => ($items['previewText'])?$items['previewText']:'N/A',
			"tax_included" => 0, 
			'cost_price' => 0,
			'unit_price' => 0,
			"override_default_tax" => 0, 
			'is_ebt_item' => 0,
			"commission_percent" => 0, 
			"commission_percent_type" => 0, 
			"commission_fixed" => 0, 
			"change_cost_price" => 0,
			'deleted' => 0, 
			"max_discount_percent" => NULL, 
			"max_edit_price" => NULL, 
			"min_edit_price" => NULL,
			'required_age'=> NULL,
			'verify_age'=> 0,
			"allow_price_override_regardless_of_permissions" => 0, 
			"only_integer" => 0, 
			'is_barcoded'=> 0,
			"default_quantity" => NULL,  
			"disable_from_price_rules" => 0,
			"dynamic_pricing" => 0, 
			"item_kit_inactive" => 0, 
			'barcode_name' => $items['name'],
			'is_favorite'=> 0,
			'loyalty_multiplier'=> NULL,	
		];

		$itemKitFieldsArray = array_keys($insertItem);

		/* Default Array Of POS Fields - Start */

		$allFields = getAllMappedFieldsFromDB($items);
		
		foreach ($allFields as $key => $fields) {
			foreach($fields as $field) {

				if ($key == 1) {
					$insertAfterItem[] = $field;
				} 
				else {

					if ($field['bitrix_field_code'] == 'selling_price') {
						/** Do Nothing */
					}
					else if (!in_array($field['pos_field_code'], $itemKitFieldsArray)) {
						continue;
					}

					if (!isset($items[$field['bitrix_field_code']]) && $field['bitrix_field_code'] != 'selling_price') {
						/** If product don't have any field move to next field. */
						continue;
					}

					if (!empty($posItem)) {
						/** Keep Current Value in case of updating existing product. Ignore if Unsubscribed Product is resubscribed. */
						if ($field['pos_update_item'] == 0 && $posItem['deleted'] == 1) {
							if ($field['pos_field_code'] != 0) {
								$insertItem[$field['pos_field_code']] = $posItem[$field['pos_field_code']];
							}
							continue;
						}					
					}

					
					if (in_array($field['pos_field_code'], ['category_id', 'weight_unit', 'supplier_id', 'unit_price'])) {
						switch ($field['pos_field_code']) {
							case 'category_id':
								$insertItem[$field['pos_field_code']] = getProductCategoryId($items[$field['bitrix_field_code']]);
							  break;
							case 'weight_unit':
								$measure = getProductWeightUnit($field['bitrix_api'].$items[$field['bitrix_field_code']]);
								if ($measure) {
									$insertItem[$field['pos_field_code']] = $measure;
								}								
							  break;
							case 'supplier_id':
								$supplierId = importSuppliers($items, $field['bitrix_field_code']);
								if ($supplierId) {
									$insertItem[$field['pos_field_code']] = $supplierId;
								}
							  break;
							case 'unit_price':
								$price = importSellingPrice($items, $field['bitrix_field_code']);
								$insertItem[$field['pos_field_code']] = $price;
								break;  
						  }
					}
					else {
						$value = $items[$field['bitrix_field_code']];
						
						if ($field['values'] == 1 && isset($allBitrixFieldsList[$field['bitrix_field_code']]['values'])) {
							/** This code collects the exact value/ Label of option based on bitrix  value id*/
							if ($field['propertyType'] == 'L') {
								$value = $allBitrixFieldsList[$field['bitrix_field_code']]['values'][$items[$field['bitrix_field_code']]['value']]['value'];
							}
						}

						/** Change status Yes/ No to pos supported 0/1 */
						if ($value == 'Yes' || $value == 'Y') {
							$value = 1;
						}
						else if ($value == 'No' || $value == 'N') {
							$value = 0;
						}
						
						if ($field['bitrix_field_code'] == 'active') {
							$value = ($value == 1)?0:1;
							continue;
						}

						if (!$value) {
							if ($field['default_value'] == 'remove_key') {
								unset($insertItem[$field['pos_field_code']]);	
							}
							else {
								if (!is_numeric($field['pos_field_code'])) {
									$insertItem[$field['pos_field_code']] = $field['default_value'];
								}								
							}														
						}
						else {
							if (!is_numeric($field['pos_field_code'])) {
								$insertItem[$field['pos_field_code']] = $value;
							}							
						}
					}				
				}
			}
		}

		if ($item_id > 0) {
			$insertItem['item_id'] = $item_id;
		}
		$categoryId = getProductCategoryId($items['iblockSectionId']);
		
		if($CI->Item_kit->save($insertItem, $item_id)){
			if ($item_id <= 0) {
				$item_id = $CI->db->insert_id();
			}
		}
		
		if ($item_id > 0) {
			$CI->db->query("UPDATE `phppos_item_kits` SET  `deleted` = '".$insertItem['deleted']."', `category_id` = '".$categoryId."'  WHERE `item_kit_id` = '".$item_id."'");

			foreach ($insertAfterItem as $field) {
				if ($field['pos_table_name'] == 'phppos_tags') {
					importItemKitTags($items, $item_id, $field['bitrix_field_code']);
				}
				else if ($field['pos_table_name'] == 'phppos_item_images') {
					importItemKitProductImage($items, $item_id, $field['bitrix_field_code'], $field);
				}
				else if ($field['pos_table_name'] == 'phppos_manufacturers') {
					importItemKitManufacturers($items, $item_id, $field['bitrix_field_code'], $field['propertyType']);
				}
			}
		}
		return $item_id;
	}

	function addItemWithSku($items, $childProducts) {
		$CI = get_instance();
		$item_id = addSimpleProduct($items);
		$return = [];
		if ($item_id > 0) {
			foreach ($childProducts as $childItem) {
				$return[$childItem['id']] = addProductVariations($item_id, $childItem, $items);
			}
			
			$variation = [];
			$allItemAttributes = $CI->Item_attribute->get_attributes_for_item_with_attribute_values($item_id);

			$variation = [];
			if (!empty($allItemAttributes)) {
				foreach ($allItemAttributes as $key => $attr) {
					$values = [];
					if (!empty($attr['attr_values'])) {
						foreach ($attr['attr_values'] as $keyIn => $value) {
							$values[$keyIn] = $value['name'];
						}
					}
					$variation['attributes'][$key] = implode("|", $values);
				}
			}

			$childKey = 0;
			$allChildProductsIds = [];
			foreach ($childProducts as $childItem) {
				$selectedAttributeValues = [];
				$attributes = [];
				$allChildProductsIds[] = $childItem['id'];

				if (isset($return[$childItem['id']]['phppos_item_attributes']) && !empty($return[$childItem['id']]['phppos_item_attributes'])) {
					foreach ($return[$childItem['id']]['phppos_item_attributes'] as $attrField) {
						if (isset($childItem[$attrField['bitrix_field_code']])) {
							if ($attrField['propertyType'] == 'L' || $attrField['propertyType'] == 'S') {
								$bitrixFieldId = str_replace("property","",$attrField['bitrix_field_code']);
	
								$attributeValue = $childItem[$attrField['bitrix_field_code']];
	
								/** Check if product have option value */
								$CI->db->select('id, attribute_id');
								$CI->db->from('phppos_attribute_values');
								$CI->db->where('bitrix_property_id', $bitrixFieldId);

								if ($attrField['propertyType'] == 'S') {
									$CI->db->where('bitrix_property_value_id', $attributeValue['valueId']);
								}
								else {
									$CI->db->where('bitrix_property_value_id', $attributeValue['value']);
								}
								
								$selectedAttributeValue = $CI->db->get()->result_array();
								if (isset($selectedAttributeValue)) {
									// $attributes[] = $selectedAttributeValue[0]['attribute_id'];
									$attributeId = $selectedAttributeValue[0]['attribute_id'];
									$selectedAttributeValues[] = $selectedAttributeValue[0]['id'];
								}
							}
						}
					}
				}

				if (!empty($selectedAttributeValues)) {
					/** Collect all attribute values selected for child product */
					$variation['item_variations']['attributes'][$childKey] = implode("|", $selectedAttributeValues);

					$CI->db->select('id');
					$CI->db->from("phppos_item_variations");
					$CI->db->where("bitrix_parent_product_id", $items['id']);
					$CI->db->where("bitrix_child_product_id", $childItem['id']);
					$query = $CI->db->get();						
					$itemVariationData = $query->result_array();

					$variationId = '';
	
					if (!empty($itemVariationData)) {
						$variationId = $itemVariationData[0]['id'];
					}
	
					$variation['item_variations']['item_variation_id'][$childKey] = $variationId;
					$variation['item_variations']['name'][$childKey] = $childItem['name'];
					$variation['item_variations']['item_number'][$childKey] = $items['id']."#".$childItem['id'];
					$variation['item_variations']['bitrix_parent_product_id'][$childKey] = $items['id'];
					$variation['item_variations']['bitrix_child_product_id'][$childKey] = $childItem['id'];
					$childKey += 1;					
				}
			}

			$item_variations_to_delete = [];
			$CI->db->select('id, bitrix_child_product_id');
			$CI->db->from("phppos_item_variations");
			$CI->db->where("bitrix_parent_product_id", $items['id']);
			$query = $CI->db->get();						
			$allItemVariationIdsData = $query->result_array();
			
			foreach($allItemVariationIdsData as $allItemVariation) {
				if (!in_array($allItemVariation['bitrix_child_product_id'], $allChildProductsIds)) {
					$item_variations_to_delete[] = $allItemVariation['id'];
				}
			}
			
			/** Generate variations for item. */
			if (isset($variation['attributes'])) {
				insert_variations($item_id, $variation['attributes'], $variation['item_variations'],$item_variations_to_delete);
			}

			foreach ($childProducts as $childItem) {
				foreach ($return[$childItem['id']]['after_product']['phppos_item_images'] as $attrField) {
					if (isset($childItem[$attrField['bitrix_field_code']])) {
						$CI->db->select('id');
						$CI->db->from("phppos_item_variations");
						$CI->db->where("bitrix_parent_product_id", $items['id']);
						$CI->db->where("bitrix_child_product_id", $childItem['id']);
						$query = $CI->db->get();						
						$itemVariationData = $query->result_array();
						$variationId = '';			
						if (!empty($itemVariationData)) {
							$variationId = $itemVariationData[0]['id'];
						}
					}
				}
			}
			
			foreach ($childProducts as $childItem) {
				$CI->db->select('id');
				$CI->db->from("phppos_item_variations");
				$CI->db->where("bitrix_parent_product_id", $items['id']);
				$CI->db->where("bitrix_child_product_id", $childItem['id']);
				$query = $CI->db->get();						
				$itemVariationData = $query->result_array();
				$variationId = '';			
				if (!empty($itemVariationData)) {
					$variationId = $itemVariationData[0]['id'];
				}						
				if (!empty($variationId) && $variationId > 0) {
					if (!empty($variationId) && $variationId > 0) {
						$sellingPrice = importSellingPrice($childItem, 'selling_price');
						if ($sellingPrice) {
							$CI->load->model("Item_variations");
							$data['unit_price'] = $sellingPrice;
							$data['cost_price'] = NULL;
							$data['promo_price'] = NULL;	
							$data['start_date'] = NULL;
							$data['end_date'] = NULL;							
							$CI->Item_variations->save($data, $variationId);
						}
					}
				}				
			}			
		}
	}

	function addProductVariations($parentItemId, $childItem, $parentItem) {
		$allFields = getAllMappedFieldsFromDB($childItem);
		$CI = get_instance();
		$beforeProduct = [];
		$afterProduct = [];
		foreach ($allFields as $key => $fields) {
			if ($key == 0) {
				foreach($fields as $field) {
					$beforeProduct[$field['pos_table_name']][] = $field;
				}
			}
			else {
				foreach($fields as $field) {
					$afterProduct[$field['pos_table_name']][] = $field;
				}
			}
		}

		$allItemAttributeValues = [];
		if (isset($beforeProduct['phppos_item_attributes'])) {
			$insertAttributesParent = $beforeProduct['phppos_item_attributes'];
			if (!empty($insertAttributesParent)) {
				foreach ($insertAttributesParent as $attribute) {
					importAttributePropertyValue($childItem, $parentItemId, $attribute['bitrix_field_code'], $attribute, getBitrixField($childItem['iblockId']));
				}
			}
		}

		$return = [
			'phppos_item_attributes' => $beforeProduct['phppos_item_attributes'], 
			'phppos_item_variations' => $beforeProduct['phppos_item_variations'],
			'after_product' => $afterProduct
		];

		return $return;
	}

	function insert_variations($item_id, $attributes_and_attr_values, $item_variations = false,$item_variations_to_delete = [])
	{
		$CI = get_instance();
		
		$CI->load->model('Item');
		$CI->load->model('Item_attribute');
		$CI->load->model('Item_attribute_value');
		$CI->load->model('Item_variations');
		
		$item_info = $CI->Item->get_info($item_id);
		
		if(!$item_info)
		{
			return false;
		}

		$attr_ids = array_keys($attributes_and_attr_values);
		
		$item_attributes_to_delete = array();
		$item_attributes_previous_result = $CI->Item_attribute->get_attributes_for_item($item_id);
		
		$attributes_previous = array();
		
		foreach($item_attributes_previous_result as $item_attr_row)
		{
			$attributes_previous[] = $item_attr_row['id'];
		}
		
		$item_attributes_to_delete = array_diff($attributes_previous,$attr_ids);
						
		foreach($item_attributes_to_delete as $item_attr_to_delete)
		{
			$CI->Item_attribute->delete_item_attribute($item_id, $item_attr_to_delete);
		}
		
	 	$CI->Item_attribute->save_item_attributes($attr_ids, $item_id);
		
		$save_item_attribute_values = array();
		
		foreach($attributes_and_attr_values as $attr_id => $attr_values)
		{
			$item_attribute_values_to_delete = array();
			$item_attribute_values_previous_result = $CI->Item_attribute_value->get_attribute_values_for_item($item_id, $attr_id)->result_array();
			$attribute_values_previous = array();
			
			foreach($item_attribute_values_previous_result as $item_attr_val_row)
			{
				$attribute_values_previous[$item_attr_val_row['attribute_value_id']] = $item_attr_val_row['attribute_value_name'];
			}
			$attr_values_array = explode('|',$attr_values);
		
			$item_attribute_values_to_delete = array_keys(array_diff($attribute_values_previous,$attr_values_array));

			foreach($item_attribute_values_to_delete as $item_attr_value_to_delete)
			{
				$CI->Item_attribute_value->delete_item_attribute_value($item_id, $item_attr_value_to_delete);
			}
						
			foreach($attr_values_array as $attr_value)
			{
				if ($attr_value)
				{
					//use save incase we want to allow term creation on variation page in future
					$attrbute_value_id = $CI->Item_attribute_value->save($attr_value, $attr_id);
					$save_item_attribute_values[] = $attrbute_value_id;
				}
			}
		}
		
		$CI->Item_attribute_value->save_item_attribute_values($item_id, $save_item_attribute_values);
		
		//variations
		if(is_array($item_variations_to_delete))
		{							
			foreach($item_variations_to_delete as $item_variation_id)
			{
				$CI->Item_variations->delete($item_variation_id);
			}
		}
		
		if (is_array($item_variations))
		{	
			$names = $item_variations['name'];
			$attribute_values = $item_variations['attributes'];
			$item_numbers = $item_variations['item_number'];
			$is_ecommerce = isset($item_variations['is_ecommerce']) ? $item_variations['is_ecommerce'] : 1;
			$item_variation_ids = $item_variations['item_variation_id'];
			$bitrix_child_product_ids = $item_variations['bitrix_child_product_id'];
			$bitrix_parent_product_ids = $item_variations['bitrix_parent_product_id'];
			
			$data = array();
					
			foreach($item_variation_ids as $key => $item_variation_id)
			{
				$attribute_ids = array();
				$attribute_value_ids = array();
				
				if ($attribute_values[$key])
				{
					$attribute_value_ids = explode("|",$attribute_values[$key]);
				}
				
				$item_variation_id = isset($item_variation_id) && $item_variation_id ? $item_variation_id : NULL;
				
				
				$all_item_numbers = explode('|',$item_numbers[$key]);
				
				$data = array(
		 			'item_id' => $item_id,
					'name' => $names[$key] == '' ? null : $names[$key],
					'item_number' => $item_numbers[$key] == '' ? null : $all_item_numbers[0],
					'is_ecommerce' => isset($is_ecommerce[$key]) && $is_ecommerce[$key] ? 1 : 0,
					'bitrix_child_product_id' => $bitrix_child_product_ids[$key],
					'bitrix_parent_product_id' => $bitrix_parent_product_ids[$key],
				);
								
				$item_variation_id = $CI->Item_variations->save($data, $item_variation_id, $attribute_value_ids);
				
				if (count($all_item_numbers) > 1)
				{
					$var_additional_item_numbers = array_slice($all_item_numbers,1);
				}
				else
				{
					$var_additional_item_numbers = array();
				}
				$CI->Additional_item_numbers->save_variation($item_id,$item_variation_id,$var_additional_item_numbers);				
			}								
		}
		
		$CI->Item->set_last_edited($item_id);
		return true;
	}	

	function addSimpleProduct($items) {
		$CI = get_instance();
		$allBitrixFieldsList = getBitrixField($items['iblockId']);
		$CI->load->model('Item');
		$itemExists = $CI->db->query("SELECT * FROM `phppos_items` WHERE `product_id` = '".$items['id']."'")->result_array();

		$item_id = -1;
		$posItem = [];
		if (count($itemExists)) {
			$posItem = $itemExists[0];
			$item_id = $posItem['item_id'];
		}

		/* Default Array Of POS Fields - Start */
		$insertItem = [
			'info_popup'=> NULL,
			'manufacturer_id'=>NULL,
			'ecommerce_product_id'=>NULL,
			'is_service'=>0,
			'allow_alt_description'=>0,
			'is_serialized'=>0,
			'is_ebt_item'=> 0,
			'is_ecommerce'=> 0,
			'verify_age'=> 0,
			'required_age'=> NULL,
			'weight'=>NULL,
			'weight_unit'=>NULL,
			'length'=>NULL,
			'width'=>NULL,
			'height'=>NULL,
			'ecommerce_shipping_class_id'=>NULL,			
			'is_series_package'=> 0,
			'is_barcoded'=> 0,
			'item_inactive'=> 0,
			'series_quantity'=> NULL,		
			'series_days_to_use_within' => NULL,	
			'is_favorite'=> 0,
			'loyalty_multiplier'=> NULL,
			'product_id' => $items['id'],
			'name' => $items['name'],
			'barcode_name' => $items['name'],
			'category_id' => getProductCategoryId($items['iblockSectionId']),
			'item_number' => $items['id'],
			'description' => ($items['previewText'])?$items['previewText']:'N/A',
			'long_description' => ($items['detailText'])?$items['detailText']:'N/A',
			'size' => 'N/A',
			'cost_price' => 0,
			'unit_price' => 0,
			'deleted' => 0
		];
		/* Default Array Of POS Fields - Start */

		$allFields = getAllMappedFieldsFromDB($items);

		$insertAfterItem = [];
		foreach ($allFields as $key => $fields) {
			foreach($fields as $field) {

				if ($key == 1) {
					$insertAfterItem[] = $field;
				} 
				else {
					if ($field['bitrix_field_code'] == 'selling_price') {
						/** Do Nothing Here */
					}
					else if (!isset($items[$field['bitrix_field_code']])) {
						/** If product don't have any field move to next field. */
						continue;
					}
					
					if (!empty($posItem)) {
						/** Keep Current Value in case of updating existing product. Ignore if Unsubscribed Product is resubscribed. */
						if ($field['pos_update_item'] == 0 && $posItem['deleted'] == 1) {
							if ($field['pos_field_code'] != 0) {
								$insertItem[$field['pos_field_code']] = $posItem[$field['pos_field_code']];
							}
							continue;
						}					
					}

					if (in_array($field['pos_field_code'], ['category_id', 'weight_unit', 'supplier_id', 'unit_price'])) {
						switch ($field['pos_field_code']) {
							case 'category_id':
								$insertItem[$field['pos_field_code']] = getProductCategoryId($items[$field['bitrix_field_code']]);
							  break;
							case 'weight_unit':
								$measure = getProductWeightUnit($field['bitrix_api'].$items[$field['bitrix_field_code']]);
								if ($measure) {
									$insertItem[$field['pos_field_code']] = $measure;
								}								
							  break;
							case 'supplier_id':
								$supplierId = importSuppliers($items, $field['bitrix_field_code']);
								if ($supplierId) {
									$insertItem[$field['pos_field_code']] = $supplierId;
								}
								break;
							case 'unit_price':
								$price = importSellingPrice($items, $field['bitrix_field_code']);
								$insertItem[$field['pos_field_code']] = $price;
								break;
						  }
					}
					else {
						$value = $items[$field['bitrix_field_code']];
						
						if ($field['values'] == 1 && isset($allBitrixFieldsList[$field['bitrix_field_code']]['values'])) {
							/** This code collects the exact value/ Label of option based on bitrix  value id*/
							if ($field['propertyType'] == 'L') {
								$value = $allBitrixFieldsList[$field['bitrix_field_code']]['values'][$items[$field['bitrix_field_code']]['value']]['value'];
							}
						}

						/** Change status Yes/ No to pos supported 0/1 */
						if ($value == 'Yes' || $value == 'Y') {
							$value = 1;
						}
						else if ($value == 'No' || $value == 'N') {
							$value = 0;
						}
						
						if ($field['bitrix_field_code'] == 'active') {
							$value = ($value == 1)?0:1;
							continue;
						}

						if (!$value) {
							if ($field['default_value'] == 'remove_key') {
								unset($insertItem[$field['pos_field_code']]);	
							}
							else {
								$insertItem[$field['pos_field_code']] = $field['default_value'];
							}														
						}
						else {
							$insertItem[$field['pos_field_code']] = $value;
						}
					}				
				}
			}
		}

		if ($item_id > 0) {
			$insertItem['item_id'] = $item_id;
		}
		
		/** Force add product id in item number field - CR by GQ */
		$insertItem['item_number'] = $items['id'];
		/** Force add product id in item number field - CR by GQ */

		if($CI->Item->save($insertItem, $item_id)){
			if ($item_id <= 0) {
				$item_id = $CI->db->insert_id();
			}
		}
		
		if ($item_id > 0) {
			$CI->db->query("UPDATE `phppos_items` SET  `deleted` = '".$insertItem['deleted']."'  WHERE `item_id` = '".$item_id."'");

			foreach ($insertAfterItem as $field) {
				if ($field['pos_table_name'] == 'phppos_tags') {
					importTags($items, $item_id, $field['bitrix_field_code']);
				}
				else if ($field['pos_table_name'] == 'phppos_item_images') {
					importProductImage($items, $item_id, $field['bitrix_field_code'], $field);
				}
				else if ($field['pos_table_name'] == 'phppos_manufacturers') {
					importManufacturers($items, $item_id, $field['bitrix_field_code'], $field['propertyType']);
				}
				else if ($field['pos_table_name'] == 'phppos_item_attributes') {
					importAttributePropertyValue($items, $item_id, $field['bitrix_field_code'], $field, $allBitrixFieldsList);
				}
			}
		}
		return $item_id;
	}

	function addSectionsToBitrix($item, $addChildProducts = false) {
		$CI = get_instance();
		$sectionId = $item['ID'];
		$sectionParentId = (empty($item['SECTION_ID']))?0:$item['SECTION_ID'];
		$categoryName = $item['NAME'];
		$parentId = NULL;
		$setParentIdIfChildExists = NULL;
		
		if ($sectionParentId > 0) {
			$parentAlreadyExists = $CI->db->query("SELECT `id` FROM `phppos_categories` WHERE `bitrix_section_id` = '".$sectionParentId."' ")->result_array();
			if (isset($parentAlreadyExists[0]['id'])) {
				$parentId = $parentAlreadyExists[0]['id'];
			}
		}

		$sectionAlreadyExists = $CI->db->query("SELECT `id` FROM `phppos_categories` WHERE `bitrix_section_id` = '".$sectionId."' AND `bitrix_section_parent_id` = '".$sectionParentId."'")->result_array();
		$insertParentId = "";
		if (!empty($parentId)) {
			$insertParentId = "`parent_id` = '".$parentId."',";
		}
		if (count($sectionAlreadyExists) > 0) {
			$CI->db->query("UPDATE `phppos_categories` SET  ".$insertParentId." `deleted` = '0', `name` = '".$categoryName."'  WHERE `bitrix_section_id` = '".$sectionId."' AND `bitrix_section_parent_id` = '".$sectionParentId."'");
			$categoryId = $CI->db->insert_id();
		}
		else {
			$CI->db->query("INSERT INTO `phppos_categories` SET ".$insertParentId." `name` = '".$categoryName."', `bitrix_section_id` = '".$sectionId."', `bitrix_section_parent_id` = '".$sectionParentId."'");
			$categoryId = $CI->db->insert_id();
			if ($categoryId && $sectionId) {
				$childAlreadyExists = $CI->db->query("SELECT `id` FROM `phppos_categories` WHERE `bitrix_section_parent_id` = '".$sectionId."'")->result_array();
				if (count($childAlreadyExists) > 0) {
					foreach ($childAlreadyExists as $childCategory) {
						$childCategoryId = $childCategory['id'];
						if ($childCategoryId) {
							$CI->db->query("UPDATE `phppos_categories` SET `parent_id` = '".$categoryId."'  WHERE `id` = '".$childCategoryId."' ");
						}						
					}
				}
			}

		}

		if ($addChildProducts) {
			addAllProductsUnderSection($sectionId);
		}
		return true;		
	}

	function addAllProductsUnderSection($sectionId, $next = 0) {
		$CI = get_instance();
		$config = getBitrixConfig();

		$inboundUlr = $config['bitrix']['inbound_url']."catalog.product.list.json?select[0]=id&select[1]=iblockId&select[2]=*&filter[iblockId]=".$config['bitrix']['parent_catalog_iblockId']."&filter[active]=Y&filter[iblockSectionId]=".$sectionId;

		if ($next > 0) {
			$inboundUlr .= "&start=".$next;
		}
		$jsonResult = callInboundAPI($inboundUlr);
		$result = json_decode($jsonResult, true);
		if (!empty($result['result']['products'])) {
			foreach($result['result']['products'] as $items) {
				if ($items['iblockSectionId'] == $sectionId)
				addProductsToBitrix($items);
			}
			if (isset($result['next']) && $result['next'] > 0) {
				return addAllProductsUnderSection($sectionId, $result['next']);
			}
			else {
				return true;
			}
			return true;
		}
		else {
			return false;
		}
	}

	function getProductFieldsMapping() {
		$config = getBitrixConfig();
		$jsonResult = callInboundAPI($config['bitrix']['inbound_url']."crm.product.fields.json");
		$result = json_decode($jsonResult, true);		
		return $result;
	}

	function getBitrixConfig($key = NULL) {
		$CI = get_instance();
		$CI->config->load('bitrix', TRUE);
		if (!empty($key)) {
			return $CI->config->item($key, 'bitrix');
		}
		else {
			return $CI->config->item('bitrix');
		}		
	}

	function getBitrixDefaultCatalogID() {
		/* Get Bitrix default catalog ID */
		$config = getBitrixConfig();
		$defaultCatalogJson = json_decode(callInboundAPI($config['bitrix']['inbound_url']."crm.catalog.list.json?SELECT[0]=ID"), true);
		return (isset($defaultCatalogJson['result'][0]['ID']))? $defaultCatalogJson['result'][0]['ID']: 24;		
	}

	function getBitrixSectionsList($next = 0, $sectionId = '') {
		$CI = get_instance();
		$CI->load->model('AllBitrixItem');
		$config = getBitrixConfig();
		$url = $config['bitrix']['inbound_url']."catalog.section.list.json?select[0]=id&select[1]=iblockId&select[2]=*&filter[active]=Y&filter[iblockId]=".getBitrixDefaultCatalogID();
		$url .= "&filter[iblockSectionId]=".$sectionId;
		if (!empty($next) && $next > 0) {
			$url .= "&start=".$next;
		}
		return json_decode(callInboundAPI($url));
	}

	function getBitrixProductsList($next = 0, $sectionId, $productId, $onlySearchByProduct = false) {
		$config = getBitrixConfig();
		$qFilter = "";
		if (!$onlySearchByProduct) {
			$qFilter .= "&filter[iblockSectionId]=".$sectionId;
		}
		$qFilter .= ($productId)?"&filter[id]=".$productId:'';

		$url = $config['bitrix']['inbound_url']."catalog.product.list.json?select[0]=id&select[1]=iblockId&filter[active]=Y&filter[iblockId]=".getBitrixDefaultCatalogID();
		$url .= $qFilter;
		if (!empty($next) && $next > 0) {
			$url .= "&start=".$next;
		}
		$allProducts = json_decode(callInboundAPI($url));
		$allProductData = [];
		if (isset($allProducts->result->products) && !empty($allProducts->result->products)) {
			foreach($allProducts->result->products as $product) {
				$productUrl = $config['bitrix']['inbound_url']."crm.product.get.json?ID=".$product->id;
				$data = json_decode(callInboundAPI($productUrl), true);
				$allProductData[] = $data['result'];
			}
		}		
		$allProducts->result->products = $allProductData;
		return $allProducts;
	}

	function setCache($key, $data, $time = NULL) {
		$CI = get_instance();
		$config = getBitrixConfig();
		$CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		if (empty($time)) {
			$time = $config['bitrix']['cache_valid_time'];
		}
		$CI->cache->save($key, $data, $time);
		return $data;
	}

	function getCache($key) {
		$CI = get_instance();
		$CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$data = $CI->cache->get($key);
		if (!empty($data)) {
			return $data;
		}
		else {
			return false;
		}
	}

	function clearCache() {
		$CI = get_instance();
		$CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));		
		$CI->cache->clean();
	}

	function callInboundAPI($inboundUrl, $condition = false) {
		$CI = get_instance();
		$response = getCache($inboundUrl);
		if ($response == false || empty($response))
		{			
			$response = $CI->curl->simple_get($inboundUrl);
			setCache($inboundUrl, $response);			 
		}
		return $response;
	}

	function lambdaAddAllItems($event,$result){
		$config = getBitrixConfig();
		$productData = $config['bitrix']['products']['events'];
		$sectionData = $config['bitrix']['sections']['events'];

		if($event == 'ONCRMPRODUCTDELETE'){
			$CI = get_instance();
			$CI->load->model('AllBitrixItem');
			if(productDelete($result['ID'])){
				$type = '2';
				if($CI->AllBitrixItem->deletebyValue($result['ID'], $type)){
					return true;
				}
			}
		}
		else if($event == 'ONCRMPRODUCTSECTIONDELETE'){
			if(categorysDelete($result['ID'], $deletbyValue = true )){
				return true;
			}
		}
		else if(in_array($event, $productData)){

			$item['item_id'] = $result['ID'];
			$item['parent_id'] = (!empty($result['SECTION_ID']))?$result['SECTION_ID']:0;
			$item['title'] = $result['NAME'];
			$item['price'] = $result['PRICE'];
			$item['currency_code'] = $result['CURRENCY_ID'];
			$item['preview_image'] = $result['PREVIEW_PICTURE']['showUrl'];
			$item['type'] = '2';
			$CI = get_instance();
			$CI->load->model('AllBitrixItem');
			$id = $CI->AllBitrixItem->getByItemIdAndType($item['item_id'], $item['type']);
			if ($id > 0) {
					$item['id'] = $id;
			}
			$CI->AllBitrixItem->save($item);
		}
		else if(in_array($event, $sectionData)){
			$item['item_id'] = $result['ID'];
			$item['parent_id'] = (!empty($result['SECTION_ID']))?$result['SECTION_ID']:0;
			$item['title'] = $result['NAME'];
			$item['price'] = '';
			$item['currency_code'] = '';
			$item['preview_image'] = '';
			$item['type'] = '1';
			$CI = get_instance();
			$CI->load->model('AllBitrixItem');
			$id = $CI->AllBitrixItem->getByItemIdAndType($item['item_id'], $item['type']);
			if ($id > 0) {
				$item['id'] = $id;
			}
			$CI->AllBitrixItem->save($item);
		}
	}

	function productDelete($productId){ 
		if (!empty($productId)) {
			$CI = get_instance();
			$CI->load->model('BitrixSubscription');
			
			
			$itemIds = $CI->db->query("SELECT `product_id`,`item_id` FROM `phppos_items` WHERE `product_id` = '".$productId."'")->result_array();	
			if (!empty($itemIds)) {
				$response = [];
				foreach($itemIds as $item){
					/*  For delete item use delete function and pass item_id */	
					if(bitrixItemDelete($item['item_id'])){
						$data = ['type' => 2, 'value' => $item['product_id']];
						if($CI->BitrixSubscription->deleteByValue($data)){
						$response = ['status' => 1 , 'message' => 'You Have Successfully Unsubscribe.' ];
						
						}
					}		
				}
				return $response;
				
			}

			$itemKitIds = $CI->db->query("SELECT `product_id`,`item_kit_id` FROM `phppos_item_kits` WHERE `product_id` = '".$productId."'")->result_array();
			if (!empty($itemKitIds)) {
				$response = [];
				foreach($itemKitIds as $item){
					/*  For delete item use delete function and pass item_id */	
					if(bitrixItemKitDelete($item['item_kit_id'])){
						$data = ['type' => 2, 'value' => $item['product_id']];
						if($CI->BitrixSubscription->deleteByValue($data)){
						$response = ['status' => 1 , 'message' => 'You Have Successfully Unsubscribe.' ];							
						}
					}		
				}
				return $response;
				
			}			
		}
	}
	function categorysDelete($sectionId, $deletbyValue = false){
		if (!empty($sectionId)) {
			$CC = get_instance();
			$CC->load->model('Category');
			/* get category id from section id for delete category and item */
			$category = $CC->db->query("SELECT `id`,`bitrix_section_id` FROM `phppos_categories` WHERE `bitrix_section_id` = '".$sectionId."'")->row();

			if (!empty($category)) {
				$CI = get_instance();
				$CI->load->model('Item');
				$CI->load->model('BitrixSubscription');
				$categoryIds = $CC->Category->get_category_id_and_children_category_ids_for_category_id($category->id);
				if(count($categoryIds) > 0){ 
					$CI->db->select('item_id,product_id');
					$CI->db->from('phppos_items');
					$CI->db->where_in('category_id',$categoryIds);
					$itemIds = $CI->db->get()->result_array(); 
					if (!empty($itemIds)) {
						foreach($itemIds as $item){
							/*  For delete item use delete function and pass item_id */	
							if(bitrixItemDelete($item['item_id'])){
								$data = ['type' => 2, 'value' => $item['product_id']];
								$CI->BitrixSubscription->deleteByValue($data);
							}
							if($deletbyValue){
								$CI = get_instance();
								$CI->load->model('AllBitrixItem');
								$type = '2';
								$CI->AllBitrixItem->deletebyValue($item['product_id'], $type);
							}					
						}
					}

					$CI->db->select('item_kit_id,product_id');
					$CI->db->from('phppos_item_kits');
					$CI->db->where_in('category_id',$categoryIds);					
					$itemKitIds = $CI->db->get()->result_array(); 
					if (!empty($itemKitIds)) {
						foreach($itemKitIds as $item){
							/*  For delete item use delete function and pass item_id */	
							if(bitrixItemKitDelete($item['item_kit_id'])){
								$data = ['type' => 2, 'value' => $item['product_id']];
								$CI->BitrixSubscription->deleteByValue($data);
							}
							if($deletbyValue){
								$CI = get_instance();
								$CI->load->model('AllBitrixItem');
								$type = '2';
								$CI->AllBitrixItem->deletebyValue($item['product_id'], $type);
							}					
						}
					}


					$CC->db->select('bitrix_section_id');
					$CC->db->select('id');
					$CC->db->from('phppos_categories');
					$CC->db->where_in('id',$categoryIds);
					$bitrixSectionIds = $CC->Category->db->get()->result_array(); 
					if (!empty($bitrixSectionIds)) {
						$bitrixSectionIdsReverse = array_reverse($bitrixSectionIds);
						foreach($bitrixSectionIdsReverse as $item){ 
							$data = ['type' => 1, 'value' => $item['bitrix_section_id']];
							$CI->BitrixSubscription->deleteByValue($data);
							categoryDeleteByValue($item['id']);
						}
					}
				}

					/* For delete category with child catergory used this function and pass catergory id only */
					$childItem = $CC->db->query("SELECT `id`,`bitrix_section_id` FROM `phppos_categories` WHERE `bitrix_section_id` = '".$category->bitrix_section_id."'")->row();
				
					if (!empty($childItem)) {
						$CI = get_instance();
						$CI->load->model('Item');
						$CI->db->select('product_id	');
						$CI->db->select('item_id');
						$CI->db->from('phppos_items');
						$CI->db->WHERE('category_id',$childItem->id);
						$itemsId = $CI->Item->db->get()->result_array(); 

						foreach($itemsId as $item){
							$CI = get_instance();
							$CI->load->model('BitrixSubscription');
							$data = ['type' => 2, 'value' => $item['product_id']];
							$CI->BitrixSubscription->deleteByValue($data);
							itemDeleteByValue($item['item_id']);
						}
						
					}
					
					categoryDeleteByValue($category->id);
					$data = ['type' => 1, 'value' => $category->bitrix_section_id ];
					$CI->BitrixSubscription->deleteByValue($data);
					if($deletbyValue){
						$CA = get_instance();
						$CA->load->model('AllBitrixItem');
						$type = '1';
						$CI->AllBitrixItem->deletebyValue($category->bitrix_section_id, $type);
					}
					$syncedItem =  $CI->db->get('phppos_bitrix_subscription')->num_rows();
					$response = ['status' => 1 , 'message' => 'You Have Succesfully Unsubscribe', 'syncedItem' => $syncedItem];
					return $response;
					
			}
		}
	}

	function itemDeleteByValue($item_id)
	{
		$CI = get_instance();
		$CI->db->where('item_id', $item_id);
		$CI->db->delete('phppos_items_pricing_history');
		return $CI->db->delete('phppos_items',array('item_id'=> $item_id));	
	}

	function bitrixItemDelete($item_id)
	{
		$CI = get_instance();
		$CI->db->where('item_id', $item_id);
		$CI->db->update('items', array('category_id' => NULL));
		return $CI->db->update('items', array('deleted' => 1));
	}

	function bitrixItemKitDelete($item_id) {
		$CI = get_instance();		
		$CI->db->where('item_kit_id', $item_id);
		$CI->db->update('item_kits', array('category_id' => NULL));		
		return $CI->db->update('item_kits', array('deleted' => 1));		
	}

	function categoryDeleteByValue($category_id)
	{		
		$CI = get_instance();
		$CI->load->model('Category');
		$CI->Category->delete_category_image($category_id);
		$CI->db->where('id', $category_id);
		return $CI->db->delete('phppos_categories');
	}