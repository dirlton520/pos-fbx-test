<?php

class BitrixConfigRequest 
{
	function getConfig()
	{
		/* Default Cache Valid for 24 Hrs, Provide Time in Seconds (60*5=300). */
		$config['bitrix']['cache_valid_time'] = 300;
		$config['bitrix']['enable_web_page_cache'] = 0;
		$config['bitrix']['refresh_product_import_grid_lambda_only'] = 1;

		/* Default Cache Valid for 24 Hrs, Provide Time in Minutes. */
		$config['bitrix']['web_page_cache_valid_time'] = 1;

		/** Config details for - API endpoint to subscribe current POS system into dynamodb - Start */
		$config['aws']['api_url'] = 'https://xrkg40muo9.execute-api.ap-south-1.amazonaws.com/subscribe-pos-hosts';
		$config['aws']['dynamodb'] = 'allposhosts_table';
		/** Config details for - API endpoint to subscribe current POS system into dynamodb - Start */

		/** Config details for - Bitrix Inbound APIs Access - Start */
		$config['bitrix']['host'] = 'https://hq.ada.asia/';
		$config['bitrix']['inbound_user_id'] = '56130';
		$config['bitrix']['inbound_key'] = 'hx1bm2tfhko32bva';
		$config['bitrix']['inbound_url'] = $config['bitrix']['host'].'rest/'.$config['bitrix']['inbound_user_id'].'/'.$config['bitrix']['inbound_key'].'/';
		$config['bitrix']['products']['events'] = [
			'ONCRMPRODUCTADD',
			'ONCRMPRODUCTUPDATE',
			'ONCRMPRODUCTDELETE'
		];
		$config['bitrix']['sections']['events'] = [
			'ONCRMPRODUCTSECTIONADD',
			'ONCRMPRODUCTSECTIONUPDATE',
			'ONCRMPRODUCTSECTIONDELETE'
		];
		/** Config details for - Bitrix Inbound APIs Access - End */
		$config['bitrix']['subscribe_child_sections'] = true;
		$config['bitrix']['subscribe_child_products'] = true;
		$config['bitrix']['parent_catalog_iblockId'] = '24';
		$config['bitrix']['parent_catalog_static_mapped_fields'] = [
			'active' => [
				'pos_field_code' => 'deleted', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 0, 
				'pos_update_item' => 1
			], 
			'code' => [
				'pos_field_code' => 'item_number', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'detailPicture' => [
				'pos_field_code' => 'main_image_id', 
				'pos_table_name' => 'phppos_item_images', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 0
			],
			'detailText' => [
				'pos_field_code' => 'long_description', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'N/A', 
				'pos_update_item' => 1
			],
			'iblockSectionId' => [
				'pos_field_code' => 'category_id', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 0, 
				'pos_update_item' => 1
			],
			'id' => [
				'pos_field_code' => 'product_id', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 0, 
				'pos_update_item' => 1
			],
			'length' => [
				'pos_field_code' => 'length', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'weight' => [
				'pos_field_code' => 'weight', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'width' => [
				'pos_field_code' => 'width', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'height' => [
				'pos_field_code' => 'height', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],			
			'measure' => [
				'pos_field_code' => 'weight_unit', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1,
				'bitrix_api' => 'catalog.measure.get.json?id='
			],
			'name' => [
				'pos_field_code' => 'name', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'N/A', 
				'pos_update_item' => 1
			],
			'previewPicture' => [
				'pos_field_code' => 0, 
				'pos_table_name' => 'phppos_item_images', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 0
			],
			'previewText' => [
				'pos_field_code' => 'description', 
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'N/A', 
				'pos_update_item' => 1
			],
			'property293' => [
				'pos_field_code' => 'Size', 
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property299' => [
				'pos_field_code' => 'Manufacturer', 
				'pos_table_name' => 'phppos_manufacturers', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property301' => [
				'pos_field_code' => 'Colour', 
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property303' => [
				'pos_field_code' => 0, 
				'pos_table_name' => 'phppos_item_images', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			// 'property304' => [
			// 	'pos_field_code' => 'barcode_name', 
			// 	'pos_table_name' => 'phppos_items', 
			// 	'pos_insert_after_item' => 0, 
			// 	'is_pos_taf' => 'table', 
			// 	'default_value' => 'remove_key', 
			// 	'pos_update_item' => 1
			// ],
			'property306' => [
				'pos_field_code' => 'Attributes', 
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property307' => [
				'pos_field_code' => 'Traits', 
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property308' => [
				'pos_field_code' => 'BaseUnit', 
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property323' => [
				'pos_field_code' => 'tags',
				'pos_table_name' => 'phppos_tags', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property324' => [
				'pos_field_code' => 'item_number',
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property880' => [
				'pos_field_code' => 0,
				'pos_table_name' => 'phppos_item_images', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property884' => [
				'pos_field_code' => 'supplier_id',
				'pos_table_name' => 'phppos_items,phppos_suppliers', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			// 'property895' => [
			// 	'pos_field_code' => 'is_barcoded',
			// 	'pos_table_name' => 'phppos_items', 
			// 	'pos_insert_after_item' => 0, 
			// 	'is_pos_taf' => 'field', 
			// 	'default_value' => 'remove_key', 
			// 	'pos_update_item' => 1
			// ],
			'property896' => [
				'pos_field_code' => 'is_serialized',
				'pos_table_name' => 'phppos_items', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			]
		];
		$config['bitrix']['child_catalog_iblockId'] = '26';
		$config['bitrix']['child_catalog_static_mapped_fields'] = [
			'active' => [
				'pos_field_code' => 'deleted', 
				'pos_table_name' => 'phppos_item_variations', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 0, 
				'pos_update_item' => 1
			], 
			'code' => [
				'pos_field_code' => 'item_number', 
				'pos_table_name' => 'phppos_item_variations', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'id' => [
				'pos_field_code' => 'product_id', 
				'pos_table_name' => 'phppos_item_variations', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 0, 
				'pos_update_item' => 1
			],
			'name' => [
				'pos_field_code' => 'name', 
				'pos_table_name' => 'phppos_item_variations', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'N/A', 
				'pos_update_item' => 1
			],
			'property92' => [
				'pos_field_code' => 'bitrix_parent_item_id', 
				'pos_table_name' => 'phppos_item_variations', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'detailPicture' => [
				'pos_field_code' => 'variation_id', 
				'pos_table_name' => 'phppos_item_images', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 0
			],
			'previewPicture' => [
				'pos_field_code' => 0, 
				'pos_table_name' => 'phppos_item_images', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 0
			],
			'property314' => [
				'pos_field_code' => 'variation_id', 
				'pos_table_name' => 'phppos_item_images', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'table', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property882' => [
				'pos_field_code' => 'variation_id',
				'pos_table_name' => 'phppos_item_images', 
				'pos_insert_after_item' => 1, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property312' => [
				'pos_field_code' => 'SizesShoes',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property881' => [
				'pos_field_code' => 'SizesClothes',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property886' => [
				'pos_field_code' => 'Gender',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property887' => [
				'pos_field_code' => 'Battery',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property888' => [
				'pos_field_code' => 'Speed',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property889' => [
				'pos_field_code' => 'RearDerailleur',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property890' => [
				'pos_field_code' => 'FrontDerailleur',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property890' => [
				'pos_field_code' => 'RearWheelSize',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property892' => [
				'pos_field_code' => 'FrontWheelSize',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property893' => [
				'pos_field_code' => 'FrameSize',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			],
			'property897' => [
				'pos_field_code' => 'DiveDestination',
				'pos_table_name' => 'phppos_item_attributes', 
				'pos_insert_after_item' => 0, 
				'is_pos_taf' => 'field', 
				'default_value' => 'remove_key', 
				'pos_update_item' => 1
			]
		];

		return $finalConfigData = base64_encode(json_encode($config));
	}
}

$class = new BitrixConfigRequest();
echo $class->getConfig(); die;

?>