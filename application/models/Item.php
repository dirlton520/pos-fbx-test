<?php
class Item extends MY_Model
{
	/*
	Determines if a given item_id is an item
	*/
	function exists($item_id)
	{
		$item_id = str_replace('|FORCE_ITEM_ID|','',$item_id);
		$this->db->from('items');
		$this->db->where('item_id',$item_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	function get_displayable_columns()
	{
		$this->load->helper('text');
		$return  = array(
			'item_id' => 												array('sort_column' => 'item_id', 'label' => lang('common_item_id')),
			'item_number' => 										array('sort_column' => 'item_number','label' => lang('common_item_number_expanded'), 'data_function' => 'item_number_data_function', 'format_function' => 'item_number_formatter'),
			'product_id' => 										array('sort_column' => 'product_id','label' => lang('common_product_id')),
			'name' => 													array('sort_column' => 'name','label' => lang('common_name'), 'data_function' => 'item_quantity_data_function','format_function' => 'item_name_formatter','html' => TRUE),
			'barcode_name' => 									array('sort_column' => 'barcode_name','label' => lang('common_barcode_name'), 'data_function' => 'item_quantity_data_function','format_function' => 'item_name_formatter','html' => TRUE),
			'category' => 											array('sort_column' => 'category','label' => lang('common_category')),
			'category_id' => 										array('sort_column' => 'category','label' => lang('common_category_full_path'),'format_function' => 'get_full_category_path'),
			'supplier_company_name' => 					array('sort_column' => 'supplier_company_name','label' => lang('common_supplier')),
			'cost_price' => 										array('sort_column' => 'cost_price','label' => lang('common_cost_price'),'format_function' => 'to_currency_and_edit_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'location_cost_price' => 						array('sort_column' => 'location_cost_price','label' => lang('common_location_cost_price'),'format_function' => 'to_currency_and_edit_location_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'location_unit_price' => 						array('sort_column' => 'location_unit_price','label' => lang('common_location_unit_price'),'format_function' => 'to_currency_and_edit_location_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'unit_price' => 										array('sort_column' => 'unit_price','label' => lang('common_unit_price'),'format_function' => 'to_currency_and_edit_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'tax_group' => 											array('sort_column' => 'tax_group','label' => lang('common_tax_class')),
			'quantity' =>												array('sort_column' => 'quantity','label' => lang('items_quantity'),'data_function' => 'item_quantity_data_function','format_function' => 'item_quantity_format', 'html' => TRUE),
			'tags' => 													array('sort_column' => 'tags','label' => lang('common_tags')),
			'description' => 										array('sort_column' => 'description','label' => lang('common_description'),'format_function' => 'clean_html', 'html' => TRUE),
			'long_description' => 							array('sort_column' => 'long_description','label' => lang('common_long_description'),'format_function' => 'clean_html', 'html' => TRUE),
			'info_popup' => 										array('sort_column' => 'info_popup','label' => lang('common_info_popup')),
			'size' => 													array('sort_column' => 'size','label' => lang('common_size')),
			'tax_included' => 									array('sort_column' => 'tax_included','label' => lang('common_prices_include_tax'),'format_function' => 'boolean_as_string'),
			'promo_price' => 										array('sort_column' => 'promo_price','label' => lang('items_promo_price'),'format_function' => 'promo_price_format'),
			'start_date' => 										array('sort_column' => 'start_date','label' => lang('items_promo_start_date'),'format_function' => 'date_as_display_date'),
			'end_date' => 											array('sort_column' => 'end_date','label' => lang('items_promo_end_date'),'format_function' => 'date_as_display_date'),
			'reorder_level' => 									array('sort_column' => 'reorder_level','label' => lang('items_reorder_level'),'format_function' => 'to_quantity'),
			'expire_days' => 										array('sort_column' => 'expire_days','label' => lang('items_days_to_expiration'),'format_function' => 'to_quantity'),
			'allow_alt_description'  => 				array('sort_column' => 'allow_alt_description','label' => lang('items_allow_alt_desciption'),'format_function' => 'boolean_as_string'),		
			'is_serialized'  => 								array('sort_column' => 'is_serialized','label' => lang('items_is_serialized'),'format_function' => 'boolean_as_string'),		
			'override_default_tax'  => 					array('sort_column' => 'override_default_tax','label' => lang('common_override_default_tax'),'format_function' => 'boolean_as_string'),		
			'is_ecommerce'  => 									array('sort_column' => 'is_ecommerce','label' => lang('items_is_ecommerce'),'format_function' => 'boolean_as_string'),		
			'ecommerce_product_id' => 					array('sort_column' => 'ecommerce_product_id','label' => lang('common_ecommerce_product_id')),
			'is_service'  => 										array('sort_column' => 'is_service','label' => lang('items_is_service'),'format_function' => 'boolean_as_string'),		
			'is_ebt_item'  => 									array('sort_column' => 'is_ebt_item','label' => lang('common_is_ebt_item'),'format_function' => 'boolean_as_string'),		
			'commission_amount'  => 						array('sort_column' => 'commission_percent','label' => lang('common_commission_amount'),'data_function' => 'commission_to_amount','format_function' => 'commission_amount_format'),		
			'commission_percent'  => 						array('sort_column' => 'commission_percent','label' => lang('items_commission_percent'),'format_function' => 'to_quantity'),		
			'commission_percent_type'  => 			array('sort_column' => 'commission_percent_type','label' => lang('items_commission_percent_type'),'format_function' => 'commission_percent_type_formater'),		
			'commission_fixed'  => 							array('sort_column' => 'commission_fixed','label' => lang('items_commission_fixed'),'format_function' => 'to_currency'),		
			'change_cost_price'  => 						array('sort_column' => 'change_cost_price','label' => lang('common_change_cost_price_during_sale'),'format_function' => 'boolean_as_string'),		
			'disable_loyalty'  => 							array('sort_column' => 'disable_loyalty','label' => lang('common_disable_loyalty'),'format_function' => 'boolean_as_string'),		
			'replenish_level'  => 							array('sort_column' => 'replenish_level','label' => lang('common_replenish_level'),'format_function' => 'to_quantity'),
			'max_discount_percent'  => 					array('sort_column' => 'max_discount_percent','label' => lang('common_max_discount_percent'),'format_function' => 'to_percent'),
			'min_edit_price'  => 								array('sort_column' => 'min_edit_price','label' => lang('common_min_edit_price'),'format_function' => 'to_currency'),
			'max_edit_price'  => 								array('sort_column' => 'max_edit_price','label' => lang('common_max_edit_price'),'format_function' => 'to_currency'),
			'has_variations' => 								array('sort_column' => 'has_variations','label' => lang('items_has_variations'),'format_function' => 'boolean_as_string_variation', 'data_function' => 'item_id_data_function','html' => TRUE),
			'variation_count' => 								array('sort_column' => 'variation_count','label' => lang('items_variation_count'),'format_function' => 'to_quantity_variation', 'data_function' => 'item_id_data_function', 'html' => TRUE),
			'last_modified' => 									array('sort_column' => 'last_modified','label' => lang('common_last_modified'),'format_function' => 'date_as_display_datetime', 'html' => TRUE),
			'last_edited' => 										array('sort_column' => 'last_edited','label' => lang('common_last_edited'),'format_function' => 'date_as_display_datetime', 'html' => TRUE),
			'weight'  => 											  array('sort_column' => 'weight','label' => lang('items_weight'),'format_function' => 'to_quantity'),
			'weight_unit'  => 											  array('sort_column' => 'weight_unit','label' => lang('items_weight_unit'),'format_function' => 'strsame'),
			'dimensions' => 								    array('sort_column' => 'length','label' => lang('items_dimensions'),'format_function' => 'dimensions_format', 'data_function' => 'dimensions_data','html' => TRUE),
			'allow_price_override_regardless_of_permissions'  => 	array('sort_column' => 'allow_price_override_regardless_of_permissions','label' => character_limiter(lang('common_allow_price_override_regardless_of_permissions'),38),'format_function' => 'boolean_as_string'),		
			'only_integer'  => 									array('sort_column' => 'only_integer','label' => character_limiter(lang('common_only_integer'),38),'format_function' => 'boolean_as_string'),		
			'is_series_package'  => 						array('sort_column' => 'is_series_package','label' => character_limiter(lang('items_sold_in_a_series'),38),'format_function' => 'boolean_as_string'),		
			'series_quantity'  => 							array('sort_column' => 'series_quantity','label' => character_limiter(lang('common_series_quantity'),38),'format_function' => 'to_quantity'),		
			'series_days_to_use_within'  => 		array('sort_column' => 'series_days_to_use_within','label' => character_limiter(lang('common_series_days_to_use_within'),38),'format_function' => 'to_quantity'),		
			'is_barcoded'  => 									array('sort_column' => 'is_barcoded','label' => lang('common_is_barcoded'),'format_function' => 'boolean_as_string'),		
			'item_inactive'  => 								array('sort_column' => 'item_inactive','label' => lang('common_inactive'),'format_function' => 'boolean_as_string'),		
			'default_quantity' =>								array('sort_column' => 'default_quantity','label' => lang('common_default_quantity'),'format_function' => 'to_quantity', 'html' => FALSE),
			'location' => 											array('sort_column' => 'location', 'label' => lang('items_location_at_store')),
			'disable_from_price_rules'  => 			array('sort_column' => 'disable_from_price_rules','label' => character_limiter(lang('common_disable_from_price_rules'),38),'format_function' => 'boolean_as_string'),		
			'is_favorite'  =>  									array('sort_column' => 'is_favorite','label' => lang('common_is_favorite'),'format_function' => 'boolean_as_string'),
			'loyalty_multiplier'  =>  					array('sort_column' => 'loyalty_multiplier', 'label' => lang('common_loyalty_multiplier'),'format_function' => 'to_quantity'),
				
		);
		
		if ($this->config->item('verify_age_for_products'))
		{
			$return['verify_age'] = array('sort_column' => 'verify_age','label' => lang('common_requires_age_verification'),'format_function' => 'boolean_as_string');		
			$return['required_age'] = array('sort_column' => 'required_age','label' => lang('common_required_age'),'format_function' => 'to_quantity');		
		}
		
		if ($this->config->item('hide_size_field'))
		{
			unset($return['size']);
		}
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->Item->get_custom_field($k) !== false)
			{
				$field = array();
				$field['sort_column'] ="custom_field_${k}_value";
				$field['label']= $this->Item->get_custom_field($k);
			
				if ($this->Item->get_custom_field($k,'type') == 'checkbox')
				{
					$format_function = 'boolean_as_string';
				}
				elseif($this->Item->get_custom_field($k,'type') == 'date')
				{
					$format_function = 'date_as_display_date';				
				}
				elseif($this->get_custom_field($k,'type') == 'email')
				{
					$this->load->helper('url');
					$format_function = 'mailto';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'url')
				{
					$this->load->helper('url');
					$format_function = 'anchor_or_blank';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'phone')
				{
					$this->load->helper('url');
					$format_function = 'tel';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'image')
				{
					$this->load->helper('url');
					$format_function = 'file_id_to_image_thumb';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'file')
				{
					$this->load->helper('url');
					$format_function = 'file_id_to_download_link';					
					$field['html'] = TRUE;
				}
				else
				{
					$format_function = 'strsame';
				}
				$field['format_function'] = $format_function;
				$return["custom_field_${k}_value"] = $field;
			}
		}
		
		return $return;
	}
	
	function get_all_offline($limit=100,$offset=0)
	{				
		$items_table = $this->db->dbprefix('items');
		$item_kits_table = $this->db->dbprefix('item_kits');
		$categories_table = $this->db->dbprefix('categories');
		$query = "(SELECT phppos_items.item_id, phppos_items.unit_price, phppos_items.promo_price,phppos_items.start_date,phppos_items.end_date, quantity, phppos_items.name as name, phppos_categories.name as category, description, item_number,product_id,tax_included FROM $items_table LEFT JOIN $categories_table ON phppos_items.category_id = phppos_categories.id LEFT JOIN phppos_location_items ON phppos_items.item_id = phppos_location_items.item_id and phppos_location_items.location_id=1
		WHERE phppos_items.deleted = 0 and system_item=0) UNION ALL (SELECT CONCAT('KIT ',item_kit_id), unit_price,NULL as promo_price,NULL as start_date, NULL as end_date,0 as quantity, phppos_item_kits.name as name,phppos_categories.name as category, description, item_kit_number,product_id,tax_included FROM $item_kits_table LEFT JOIN $categories_table ON phppos_item_kits.category_id = phppos_categories.id 
		WHERE phppos_item_kits.deleted = 0) LIMIT $limit OFFSET $offset";
		
		return $this->db->query($query);
		
	}
	
	/*
	Returns all the items
	*/
	function get_all($deleted=0,$limit=10000, $offset=0,$col='item_id',$order='desc')
	{

		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		$phppos_location_ban_items = $this->db->dbprefix('location_ban_items');
		$see_all_items = $this->Employee->has_module_action_permission('items','see_all_items',$this->Employee->get_logged_in_employee_info()->person_id);

		if (!$deleted)
		{
			$deleted = 0;
		}

		$this->load->model('Item_attribute');
		$attribute_count = $this->Item_attribute->count_all();
		$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		if ($attribute_count > 0 && !$this->config->item('speed_up_search_queries'))
		{
			$col = $this->db->escape_str($col);
			$order = $this->db->escape_str($order);
			$limit = $this->db->escape_str($limit);
			
			$order_by = '';
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$order_by = "ORDER BY $col $order";
			}
			
			$offset = $this->db->escape_str($offset);
			$deleted = $this->db->escape_str($deleted);
			$phppos_categories = $this->db->dbprefix('categories');
			$phppos_tags = $this->db->dbprefix('tags');
			$phppos_items_tags = $this->db->dbprefix('items_tags');
			$phppos_suppliers = $this->db->dbprefix('suppliers');
			$phppos_location_items = $this->db->dbprefix('location_items');
			$phppos_items = $this->db->dbprefix('items');
			$phppos_location_item_variations = $this->db->dbprefix('location_item_variations');
			$phppos_tax_classes = $this->db->dbprefix('tax_classes');
			$phppos_item_images = $this->db->dbprefix('item_images');
			$phppos_item_variations = $this->db->dbprefix('item_variations');
			
			$query = "SELECT SQL_CALC_FOUND_ROWS main_query.*
			FROM (
				SELECT ".$this->db->escape(lang('common_inv'))." as `inventory`, `$phppos_suppliers`.`company_name` as `supplier_company_name`, `$phppos_location_items`.`location` as `location`, `$phppos_items`.*, 
				`$phppos_categories`.`name` as `category`,
				 SUM(if($phppos_item_variations.deleted=0,1, 0)) as variation_count,  IF(SUM(if($phppos_item_variations.deleted=0,1, 0))  > 0,1,0) as has_variations,
				`$phppos_location_items`.`reorder_level` as `location_reorder_level`, 
				`$phppos_location_items`.`cost_price` as `location_cost_price`, `$phppos_location_items`.`unit_price` as `location_unit_price`, `$phppos_tax_classes`.`name` as `tax_group`,
				 IF(SUM(if($phppos_item_variations.deleted=0,1, 0))  > 0,SUM(IF($phppos_item_variations.deleted=0, $phppos_location_item_variations.quantity, 0)),$phppos_location_items.quantity) as quantity,
				 `$phppos_items`.`main_image_id` as image_id
	        FROM `$phppos_items`
					LEFT JOIN `$phppos_location_items` 
						ON `$phppos_location_items`.`item_id` = `$phppos_items`.`item_id` and `$phppos_location_items`.`location_id` = $current_location
	        LEFT JOIN `$phppos_item_variations` 
	           ON `$phppos_item_variations`.`item_id` = `$phppos_items`.`item_id` 
	        LEFT JOIN `$phppos_location_item_variations` 
	          ON `$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location
					LEFT JOIN `$phppos_tax_classes` 
						ON `$phppos_tax_classes`.`id` = $phppos_items.tax_class_id
					LEFT JOIN `$phppos_suppliers` 
						ON $phppos_items.supplier_id = `$phppos_suppliers`.`person_id` 
					LEFT JOIN `$phppos_categories` 
						ON `$phppos_categories`.`id` = $phppos_items.category_id".
					(!$see_all_items?
					" LEFT JOIN `$phppos_location_ban_items`
						ON (`$phppos_location_ban_items`.`item_id` = $phppos_items.item_id and `$phppos_location_ban_items`.`location_id` = $location_id)
					WHERE `$phppos_location_ban_items`.`item_id` is NULL ":" ").
					"GROUP BY `$phppos_items`.`item_id` ) as main_query
	        WHERE deleted = '$deleted' and system_item = 0 $order_by
	        LIMIT $limit OFFSET $offset";
							
					return $this->db->query($query);
		}
		else
		{
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->protect_identifiers('SQL_CALC_FOUND_ROWS');
			  $this->db->select('SQL_CALC_FOUND_ROWS 1 as _hacked', false);
				$this->db->select($this->db->escape(lang('common_inv')).' as inventory, suppliers.company_name as supplier_company_name, location_items.location as location, items.*, items.main_image_id as image_id, categories.id as category_id, categories.name as category,
				location_items.quantity as quantity,0 as variation_count, 0 as has_variations,
				location_items.reorder_level as location_reorder_level,
				location_items.cost_price as location_cost_price,
				location_items.unit_price as location_unit_price,
				tax_classes.name as tax_group
				');
			}
			else
			{
				$this->db->protect_identifiers('SQL_CALC_FOUND_ROWS');
			  $this->db->select('SQL_CALC_FOUND_ROWS 1 as _hacked', false);
				$this->db->select($this->db->escape(lang('common_inv')).' as inventory, suppliers.company_name as supplier_company_name, location_items.location as location, items.*, items.main_image_id as image_id, categories.id as category_id, categories.name as category,
				location_items.quantity as quantity,0 as variation_count, 0 as has_variations, 
				location_items.reorder_level as location_reorder_level,
				location_items.cost_price as location_cost_price,
				location_items.unit_price as location_unit_price,
				"" as tax_group
				');
			}
			
			$this->db->from('items');
			$this->db->join('tax_classes', 'tax_classes.id = items.tax_class_id', 'left');
			$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
			$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id = '.$current_location, 'left');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			if(!$see_all_items)
				$this->db->join('location_ban_items', 'location_ban_items.item_id = items.item_id and location_ban_items.location_id = '.$location_id, 'left');
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->group_by('items.item_id');
			}
			$this->db->where('items.deleted', $deleted);
			$this->db->where('items.system_item', 0);
			if(!$see_all_items)
				$this->db->where('location_ban_items.location_id Is NULL');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->order_by($col, $order);
			}
		
			$this->db->limit($limit);
			$this->db->offset($offset);
			$return = $this->db->get();
			return $return;
		}
	}

	function get_all_by_supplier($supplier_id)
	{
		$this->db->from('items');
		$this->db->where('supplier_id', $supplier_id);
		$this->db->where('items.deleted',0);
		$this->db->where('items.system_item',0);
		$this->db->order_by('name');
		
		return $this->db->get()->result_array();
	}
	
	function get_all_by_category($category_id, $hide_out_of_stock_grid = FALSE, $offset=0, $limit = 14,$show_hidden=FALSE)
	{
		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		$items_table = $this->db->dbprefix('items');
		$item_kits_table = $this->db->dbprefix('item_kits');
		$item_images_table = $this->db->dbprefix('item_images');
		$location_ban_item_table = $this->db->dbprefix('location_ban_items');
		$location_ban_item_kit_table = $this->db->dbprefix('location_ban_item_kits');

		$location_ban_item_query_left_join = " LEFT JOIN $location_ban_item_table ON( $items_table.item_id = $location_ban_item_table.item_id and $location_ban_item_table.location_id = $location_id) ";
		$location_ban_item_query_where = " and $location_ban_item_table.item_id is NULL ";
		$location_ban_item_kit_query_left_join = " LEFT JOIN $location_ban_item_kit_table ON( $item_kits_table.item_kit_id = $location_ban_item_kit_table.item_kit_id and $location_ban_item_kit_table.location_id = $location_id) ";
		$location_ban_item_kit_query_where = " and $location_ban_item_kit_table.item_kit_id is NULL ";

		if (!$hide_out_of_stock_grid)
		{
			$result = $this->db->query("
			(
				SELECT 
					$items_table.item_id, unit_price, name, size, COALESCE(phppos_items.main_image_id,$item_images_table.image_id) as image_id 
				FROM 
					$items_table LEFT JOIN $item_images_table on ($items_table.item_id = $item_images_table.item_id) $location_ban_item_query_left_join
				WHERE 
					item_inactive = 0 and deleted = 0 and system_item = 0 and 
					(
						category_id = $category_id or $items_table.item_id IN 
						(
							SELECT item_id FROM phppos_items_secondary_categories WHERE phppos_items_secondary_categories.item_id = $items_table.item_id and category_id=$category_id
						)
					)
					and $items_table.item_id NOT IN 
					( 
						SELECT item_id FROM phppos_grid_hidden_items WHERE location_id=$location_id
					) 
					$location_ban_item_query_where 
					GROUP BY $items_table.item_id ORDER BY name
			) 
			UNION ALL
			(
				SELECT CONCAT('KIT ',$item_kits_table.item_kit_id), unit_price, name, '', main_image_id as image_id
				FROM $item_kits_table $location_ban_item_kit_query_left_join
				WHERE 
					item_kit_inactive = 0 and deleted = 0 $location_ban_item_kit_query_where and 
					(	
						category_id = $category_id or $item_kits_table.item_kit_id IN 
						(
							SELECT item_kit_id FROM phppos_item_kits_secondary_categories WHERE item_kit_id = phppos_item_kits.item_kit_id and category_id=$category_id
						)
					) 
					and $item_kits_table.item_kit_id NOT IN 
					(
						SELECT item_kit_id FROM phppos_grid_hidden_item_kits WHERE location_id=$location_id
					) 
					ORDER BY name
			) ORDER BY name LIMIT $offset, $limit");
		}
		else
		{
			$location_items_table = $this->db->dbprefix('location_items ');
			$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
			$result = $this->db->query("
			(
				SELECT $items_table.item_id, $items_table.unit_price, name,size, COALESCE( $items_table.main_image_id,$item_images_table.image_id) as image_id 
				FROM $items_table 
					LEFT JOIN $item_images_table ON( $items_table.item_id =  $item_images_table.image_id) 
					$location_ban_item_query_left_join 
					LEFT JOIN $location_items_table as li ON $items_table.item_id = li.item_id and li.location_id = $current_location
				WHERE 
					item_inactive = 0 and (quantity > 0 or quantity IS NULL or is_service = 1) and deleted = 0 and system_item = 0 $location_ban_item_query_where and 
					(
						category_id = $category_id or $items_table.item_id IN 
						(
							SELECT item_id FROM phppos_items_secondary_categories WHERE item_id = $items_table.item_id and category_id=$category_id
						)
					) and $items_table.item_id NOT IN (SELECT item_id FROM phppos_grid_hidden_items WHERE location_id=$location_id) 
					
				GROUP BY item_id ORDER BY name
			) 
			UNION ALL 
			(
				SELECT CONCAT('KIT ',$item_kits_table.item_kit_id), unit_price, name, '', main_image_id as image_id 
				FROM $item_kits_table $location_ban_item_kit_query_left_join
				WHERE 
					item_kit_inactive = 0 and deleted = 0 $location_ban_item_kit_query_where and 
					(
						category_id = $category_id or $item_kits_table.item_kit_id IN 
						(
							SELECT item_kit_id FROM phppos_item_kits_secondary_categories WHERE item_kit_id = $item_kits_table.item_kit_id and category_id=$category_id
						)
					) 
					and $item_kits_table.item_kit_id NOT IN (SELECT item_kit_id FROM phppos_grid_hidden_item_kits WHERE location_id=$location_id) 
				ORDER BY name
			) ORDER BY name LIMIT $offset, $limit");
		}
		return $result;
	}
	
	function count_all_by_category($category_id)
	{		
		$this->db->from('items');
		$this->db->where('deleted',0);
		$this->db->where('system_item',0);
		$this->db->where('category_id',$category_id);
		$items_count = $this->db->count_all_results();

		$this->db->from('item_kits');
		$this->db->where('deleted',0);
		$this->db->where('category_id',$category_id);
		$item_kits_count = $this->db->count_all_results();
		
		return $items_count + $item_kits_count;

	}
	
	function get_all_by_tag($tag_id, $hide_out_of_stock_grid = FALSE, $offset=0, $limit = 14)
	{
		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		$items_table = $this->db->dbprefix('items');
		$items_images_table = $this->db->dbprefix('item_images');
		$items_tags_table = $this->db->dbprefix('items_tags');
		
		$item_kits_table = $this->db->dbprefix('item_kits');
		$item_kits_tags_table = $this->db->dbprefix('item_kits_tags');
		
		if (!$hide_out_of_stock_grid)
		{
			$result = $this->db->query("(SELECT item_id, unit_price,name, image_id FROM $items_table LEFT JOIN $items_images_table USING (item_id) INNER JOIN $items_tags_table USING (item_id)
			WHERE deleted = 0 and system_item = 0 and $items_tags_table.tag_id = $tag_id and $items_table.item_id NOT IN (SELECT item_id FROM phppos_grid_hidden_items WHERE location_id=$location_id) GROUP BY item_id ORDER BY name) UNION ALL (SELECT CONCAT('KIT ',item_kit_id), unit_price, name, main_image_id as image_id FROM $item_kits_table INNER JOIN $item_kits_tags_table USING (item_kit_id)
			WHERE deleted = 0 and $item_kits_tags_table.tag_id = $tag_id and $item_kits_table.item_kit_id NOT IN (SELECT item_kit_id FROM phppos_grid_hidden_item_kits WHERE location_id=$location_id) ORDER BY name) ORDER BY name LIMIT $offset, $limit");
		}
		else
		{
			$location_items_table = $this->db->dbprefix('location_items ');
			$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
			$result = $this->db->query("(SELECT i.item_id, i.unit_price, name,size, image_id FROM $items_table as i LEFT JOIN $items_images_table USING (item_id) INNER JOIN $items_tags_table USING (item_id) LEFT JOIN $location_items_table as li ON i.item_id = li.item_id and li.location_id = $current_location
			WHERE (quantity > 0 or quantity IS NULL or is_service = 1) and deleted = 0 and system_item = 0 and $items_tags_table.tag_id = $tag_id and i.item_id NOT IN (SELECT item_id FROM phppos_grid_hidden_items WHERE location_id=$location_id) GROUP BY i.item_id ORDER BY name) UNION ALL (SELECT CONCAT('KIT ',item_kit_id), unit_price, name, '', main_image_id as image_id FROM $item_kits_table INNER JOIN $item_kits_tags_table USING (item_kit_id)
			WHERE deleted = 0 and $item_kits_tags_table.tag_id = $tag_id and $item_kits_table.item_kit_id NOT IN (SELECT item_kit_id FROM phppos_grid_hidden_item_kits WHERE location_id=$location_id) ORDER BY name) ORDER BY name LIMIT $offset, $limit");
		}
		
		return $result;
	}
	
	function get_all_favorite_items($hide_out_of_stock_grid = FALSE, $offset=0, $limit = 14)
	{
		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		$items_table = $this->db->dbprefix('items');
		$items_images_table = $this->db->dbprefix('item_images');
		
		$item_kits_table = $this->db->dbprefix('item_kits');
		$item_kits_tags_table = $this->db->dbprefix('item_kits_tags');
		
		if (!$hide_out_of_stock_grid)
		{
			$result = $this->db->query("(
			SELECT item_id, unit_price,name, image_id, size
			FROM $items_table 
			LEFT JOIN $items_images_table USING (item_id) 
			WHERE deleted = 0 
			and system_item = 0 
			and $items_table.item_id NOT IN (SELECT item_id FROM phppos_grid_hidden_items WHERE location_id=$location_id) 
			and $items_table.is_favorite = 1
			GROUP BY item_id ORDER BY name) 
			
			UNION ALL (
			SELECT CONCAT('KIT ',item_kit_id), unit_price, name, main_image_id as image_id,'' as SIZE
			FROM $item_kits_table 
			WHERE deleted = 0 
			and $item_kits_table.is_favorite = 1
			and $item_kits_table.item_kit_id NOT IN (SELECT item_kit_id FROM phppos_grid_hidden_item_kits WHERE location_id=$location_id) 
			ORDER BY name) 
			
			ORDER BY name LIMIT $offset, $limit");
		}
		else
		{
			$location_items_table = $this->db->dbprefix('location_items ');
			$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
			$result = $this->db->query("(SELECT i.item_id, i.unit_price, name,size, image_id FROM $items_table as i LEFT JOIN $items_images_table USING (item_id) 
			LEFT JOIN $location_items_table as li ON i.item_id = li.item_id and li.location_id = $current_location
			WHERE (quantity > 0 or quantity IS NULL or is_service = 1) 
			and deleted = 0 
			and system_item = 0 
			and i.is_favorite = 1
			and i.item_id NOT IN (SELECT item_id FROM phppos_grid_hidden_items WHERE location_id=$location_id) GROUP BY i.item_id ORDER BY name) 
			
			UNION ALL 
			
			(SELECT CONCAT('KIT ',item_kit_id), unit_price, name, '', 'no_image' as image_id 
			FROM $item_kits_table 
			WHERE deleted = 0 and $item_kits_table.is_favorite = 1 and $item_kits_table.item_kit_id NOT IN (SELECT item_kit_id FROM phppos_grid_hidden_item_kits WHERE location_id=$location_id) ORDER BY name) LIMIT $offset, $limit");
		}
		
		return $result;
	}

	function get_all_item_by_supplier($supplier_id, $hide_out_of_stock_grid = FALSE, $offset=0, $limit = 14)
	{
		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		$items_table = $this->db->dbprefix('items');
		$items_images_table = $this->db->dbprefix('item_images');
		
		if (!$hide_out_of_stock_grid)
		{
			$result = $this->db->query("
			SELECT item_id, unit_price,name, image_id, size
			FROM $items_table 
			LEFT JOIN $items_images_table USING (item_id) 
			WHERE deleted = 0 
			and system_item = 0 
			and $items_table.item_id NOT IN (SELECT item_id FROM phppos_grid_hidden_items WHERE location_id=$location_id) 
			and $items_table.supplier_id = $supplier_id
			GROUP BY item_id 
			ORDER BY name LIMIT $offset, $limit");
		}
		else
		{
			$location_items_table = $this->db->dbprefix('location_items ');
			$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
			$result = $this->db->query("
			SELECT i.item_id, i.unit_price, name,size, image_id 
			FROM $items_table as i 
			LEFT JOIN $items_images_table USING (item_id) 
			LEFT JOIN $location_items_table as li ON i.item_id = li.item_id and li.location_id = $current_location
			WHERE (quantity > 0 or quantity IS NULL or is_service = 1) 
			and deleted = 0 
			and system_item = 0 
			and i.supplier_id = $supplier_id
			and i.item_id NOT IN (SELECT item_id FROM phppos_grid_hidden_items WHERE location_id=$location_id) GROUP BY i.item_id ORDER BY name LIMIT $offset, $limit");
		}
		
		return $result;
	}
	
	function get_ecommerce_product_id($item_id)
	{
		$this->db->from('items');
		$this->db->where('item_id',$item_id);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->ecommerce_product_id;
		}
		else
		{
			return NULL;
		}
	}
	
	
	function count_all_by_tag($tag_id)
	{
		$this->db->from('items_tags');
		$this->db->where('tag_id',$tag_id);
		$items_count = $this->db->count_all_results();

		$this->db->from('item_kits_tags');
		$this->db->where('tag_id',$tag_id);
		$item_kits_count = $this->db->count_all_results();
		
		return $items_count + $item_kits_count;
	}
	
	function count_all_favorite_items()
	{
		$this->db->from('items');
		
		if($this->config->item('hide_out_of_stock_grid') == TRUE){
			$current_location = $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
			$this->db->join("location_items","items.item_id = location_items.item_id and location_items.location_id = $current_location",'left');
			$this->db->or_where('quantity > ',0);
			$this->db->or_where('quantity is null',null, false);
		}
		
		$this->db->where('is_favorite',1);
		$this->db->where('deleted',0);
		$this->db->where('system_item',0);
		
		$items_count = $this->db->count_all_results();

		$this->db->from('item_kits');
		$this->db->where('deleted',0);
		$this->db->where('is_favorite',1);
		$item_kits_count = $this->db->count_all_results();
		
		return $items_count + $item_kits_count;
	}
		
	function get_next_id($item_id)
	{
		$items_table = $this->db->dbprefix('items');
		$result = $this->db->query("SELECT item_id FROM $items_table WHERE system_item= 0 and item_id = (select min(item_id) from $items_table where deleted = 0 and item_id > ".$this->db->escape($item_id).")");
		
		if($result->num_rows() > 0)
		{
			$row = $result->result();
			return $row[0]->item_id;
		}
		
		return FALSE;
	}
	
	function get_prev_id($item_id)
	{
		$items_table = $this->db->dbprefix('items');
		$result = $this->db->query("SELECT item_id FROM $items_table WHERE system_item = 0 and item_id = (select max(item_id) from $items_table where deleted = 0 and item_id <".$this->db->escape($item_id).")");
		
		if($result->num_rows() > 0)
		{
			$row = $result->result();
			return $row[0]->item_id;
		}
		
		return FALSE;
	}
	
	function get_all_tiers_prices()
	{
		$this->db->from('items_tier_prices');
		$result = $this->db->get();
		
		$return = array();
		while($row = $result->unbuffered_row('array'))
		{
			$return[$row['item_id']][$row['tier_id']] = array('unit_price' => $row['unit_price'], 'percent_off' => $row['percent_off'], 'cost_plus_percent' => $row['cost_plus_percent'],'cost_plus_fixed_amount' => $row['cost_plus_fixed_amount']);
		}
		
		return $return;
	}
	
	function get_tier_price_row($tier_id,$item_id)
	{
		$this->db->from('items_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('item_id ',$item_id);
		return $this->db->get()->row();
	}
		
	function delete_tier_price($tier_id, $item_id)
	{
		$this->db->where('tier_id', $tier_id);
		$this->db->where('item_id', $item_id);
		return $this->db->delete('items_tier_prices');
	}
	
	function delete_all_tier_prices($item_id)
	{
		$this->db->where('item_id', $item_id);
		return $this->db->delete('items_tier_prices');
	}
	
	function tier_exists($tier_id, $item_id)
	{
		$this->db->from('items_tier_prices');
		$this->db->where('tier_id',$tier_id);
		$this->db->where('item_id',$item_id);
		$query = $this->db->get();

		return ($query->num_rows()>=1);
		
	}
	
	function save_item_tiers($tier_data, $item_id)
	{
		if($this->tier_exists($tier_data['tier_id'],$item_id))
		{
			$this->db->where('tier_id', $tier_data['tier_id']);
			$this->db->where('item_id', $item_id);

			return $this->db->update('items_tier_prices',$tier_data);
			
		}

		return $this->db->insert('items_tier_prices',$tier_data);	
	}


	function account_number_exists($item_number)
	{
		$this->db->from('items');	
		$this->db->where('item_number',$item_number);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}

	function product_id_exists($product_id)
	{
		$this->db->from('items');	
		$this->db->where('product_id',$product_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
		
	function get_item_images($item_id)
	{
		$this->db->from('item_images');
		$this->db->where('item_id',$item_id);
		$this->db->order_by('id');
	  return $this->db->get()->result_array();
	}
	
	function count_all($deleted=0)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		$see_all_items = $this->Employee->has_module_action_permission('items','see_all_items',$this->Employee->get_logged_in_employee_info()->person_id);
		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		$this->db->from('items');
		if(!$see_all_items){
			$this->db->join('location_ban_items', 'items.item_id = location_ban_items.item_id and location_ban_items.location_id = '.$location_id,'left');
			$this->db->where('location_ban_items.location_id IS NULL');
		}

		$this->db->where('deleted',$deleted);
		$this->db->where('system_item', 0);
		return $this->db->count_all_results();
	}
	
	/*
	Gets information about a particular item
	*/
	function get_info($item_id, $can_cache = TRUE)
	{
		$item_id = str_replace('|FORCE_ITEM_ID|','',$item_id);
		
		if ($can_cache)
		{
			static $cache  = array();
		}		
		else
		{
			$cache = array();
		}
		
		if (is_array($item_id))
		{
			$items = $this->get_multiple_info($item_id)->result();
			foreach($items as $item)
			{
				//We don't need image in the case we are passing in array; adding images would slow performance
				$item->image_id= '';				
				$cache[$item->item_id] = $item;
			}
			
			return $items;
		}
		else
		{
			if (isset($cache[$item_id]))
			{
				return $cache[$item_id];
			}
		}
		
		
		//If we are NOT an int return empty item
		if (!is_numeric($item_id))
		{
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = array('weight_unit','ecommerce_inventory_item_id','barcode_name','info_popup','name','category_id','supplier_id','manufacturer_id','item_number','product_id','ecommerce_product_id','ecommerce_product_quantity','description','size','tax_included','cost_price','unit_price','promo_price','start_date','end_date','reorder_level','expire_days','item_id','allow_alt_description','is_serialized','override_default_tax','is_ecommerce','is_service','is_ebt_item','commission_percent','commission_percent_type','commission_fixed','change_cost_price','disable_loyalty','deleted','last_modified','ecommerce_last_modified','tax_class_id','replenish_level','system_item','max_discount_percent','max_edit_price','min_edit_price','custom_field_1_value','custom_field_2_value','custom_field_3_value','custom_field_4_value','custom_field_5_value','custom_field_6_value','custom_field_7_value','custom_field_8_value','custom_field_9_value','custom_field_10_value','required_age','verify_age','weight','length','width','height','ecommerce_shipping_class_id','long_description','allow_price_override_regardless_of_permissions','main_image_id','only_integer','is_series_package','series_quantity','series_days_to_use_within','is_barcoded','item_inactive','default_quantity','disable_from_price_rules','is_favorite','loyalty_multiplier');

			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}
			
			$item_obj->image_id='';
			
			return $item_obj;	
		}
			
		$this->db->from('items');
		$this->db->where('item_id',$item_id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$cache[$item_id] = $query->row();
			$cache[$item_id]->image_id = $cache[$item_id]->main_image_id;
			return $cache[$item_id];
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = array('weight_unit','ecommerce_inventory_item_id','barcode_name','info_popup','name','category_id','supplier_id','manufacturer_id','item_number','product_id','ecommerce_product_id','ecommerce_product_quantity','description','size','tax_included','cost_price','unit_price','promo_price','start_date','end_date','reorder_level','expire_days','item_id','allow_alt_description','is_serialized','override_default_tax','is_ecommerce','is_service','is_ebt_item','commission_percent','commission_percent_type','commission_fixed','change_cost_price','disable_loyalty','deleted','last_modified','ecommerce_last_modified','tax_class_id','replenish_level','system_item','max_discount_percent','max_edit_price','min_edit_price','custom_field_1_value','custom_field_2_value','custom_field_3_value','custom_field_4_value','custom_field_5_value','custom_field_6_value','custom_field_7_value','custom_field_8_value','custom_field_9_value','custom_field_10_value','required_age','verify_age','weight','length','width','height','ecommerce_shipping_class_id','long_description','allow_price_override_regardless_of_permissions','main_image_id','only_integer','is_series_package','series_quantity','series_days_to_use_within','is_barcoded','item_inactive','default_quantity','is_favorite','loyalty_multiplier');

			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}
				$item_obj->image_id='';
			

			return $item_obj;
		}
	}
	
	function get_category($category_id)
	{
		$this->db->from('categories');
		$this->db->where('id', $category_id);
		
		$query = $this->db->get();
		
		if($query->num_rows() >= 1)
		{
			return $query->row()->name;
		}
		$this->lang->load('error');
		return lang('error_unknown');
		
	}
	
	//returns an int or false
	public function lookup_item_id($item_identifer,$skip_lookup = array())
	{	
		if (($item_identifer_parts = explode('#', $item_identifer)) !== false)
		{
				$item_identifer = $item_identifer_parts[0];
		}
		
		$result = false;
    $item_lookup_order = unserialize($this->config->item('item_lookup_order'));
			
		foreach($item_lookup_order as $item_lookup_number)
		{
			switch ($item_lookup_number) 
			{
			    case 'item_id':
							if (!in_array('item_id',$skip_lookup))
							{
								$result = $this->lookup_item_by_item_id($item_identifer);
							}
						  break;
			    case 'item_number':
							if (!in_array('item_number',$skip_lookup))
							{
			       	 $result = $this->lookup_item_by_item_number($item_identifer);
						 	}
							break;
					case 'item_variation_item_number':
							if(!in_array('item_variation_item_number',$skip_lookup))
							{
								$result = $this->lookup_item_by_item_variation_item_number($item_identifer);
							}
							
							if(!in_array('item_variation_item_number',$skip_lookup))
							{
								if ($result === FALSE)
								{
									$result = $this->lookup_item_by_item_variation_item_number_quantity_unit($item_identifer);
								}
							}							
							break;
			    case 'product_id':
							if (!in_array('product_id',$skip_lookup))
							{
				    		$result = $this->lookup_item_by_product_id($item_identifer);
			        }
							
							break;
					case 'additional_item_numbers':
							if (!in_array('additional_item_numbers',$skip_lookup))
							{
	       	 			$result = $this->lookup_item_by_additional_item_numbers($item_identifer);
							}
							break;
					case 'serial_numbers':
							if (!in_array('serial_numbers',$skip_lookup))
							{
   	 						$result = $this->lookup_item_by_serial_number($item_identifer);
							}
							break;	
			}

			if ($result !== FALSE)
			{

				$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
				if($this->is_item_ban($result,$location_id)){
					$result = FALSE;
				}else{
					return $result;
				}
			}
		}
		
		return FALSE;
	}

	private function lookup_item_by_item_id($item_id)
	{
		$item_id = str_replace('|FORCE_ITEM_ID|','',$item_id);
		
		if (does_contain_only_digits($item_id))
		{
			if($this->exists($item_id))
			{
				return (int)$item_id;
			}	
	
		}	
		return false;
	}
	
	//return item_id
	private function lookup_item_by_item_number($item_number)
	{
		$this->db->from('items');
		$this->db->where('item_number',$item_number);

		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			return $query->row()->item_id;
		}
		
		return false;
	}
	
	private function lookup_item_by_item_variation_item_number($item_identifer)
	{
		$this->load->model('Item_variations');
		
		return $this->Item_variations->lookup_item_by_item_variation_item_number($item_identifer);
	}
	
	private function lookup_item_by_item_variation_item_number_quantity_unit($item_identifer)
	{
		$this->load->model('Item_variations');
		
		return $this->Item_variations->lookup_item_by_item_variation_item_number_quantity_unit($item_identifer);
	}
	
	
	private function lookup_item_by_product_id($product_id)
	{
		$this->db->from('items');
		$this->db->where('product_id', $product_id); 

		$query = $this->db->get();

		if($query->num_rows() >= 1)
		{
			return $query->row()->item_id;
		}
		
		return false;
	}
	
	private function lookup_item_by_additional_item_numbers($item_number)
	{
		$this->load->model('Additional_item_numbers');
		if ($additional_item_id = $this->Additional_item_numbers->get_item_id($item_number))
		{
			return $additional_item_id;
		}
		
		return false;
	}
	
	private function lookup_item_by_serial_number($serial_number)
	{
		$this->load->model('Item_serial_number');
		
		if ($item_id_from_serial_number = $this->Item_serial_number->get_item_id($serial_number))
		{
			
			$variation_id_from_serial_number = $this->Item_serial_number->get_variation_id($serial_number);
			
			return $item_id_from_serial_number. ($variation_id_from_serial_number ? '#'.$variation_id_from_serial_number : '');
		}

		return false;
	}
	
	/*
	Get an item id given an item number or product_id or additional item number
	*/
	function get_item_id($item_identifer)
	{
		return $this->lookup_item_id($item_identifer,array('item_id'));
	}

	/*
	Gets information about multiple items
	*/
	function get_multiple_info($item_ids)
	{
		$this->db->from('items');
		if (!empty($item_ids))
		{
			$this->db->group_start();
			$item_ids_chunk = array_chunk($item_ids,25);
			foreach($item_ids_chunk as $item_ids)
			{
				$this->db->or_where_in('item_id',$item_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		$this->db->order_by("item_id", "asc");
		return $this->db->get();
	}

	/*
	Inserts or updates a item
	*/
	function save(&$item_data, $item_id = false)
	{
		if(isset($item_data['ecommerce_last_modified']))
		{//if comming from ecommerce
			if(!$item_id)
			{//new item
				$item_data['last_modified'] = $item_data['ecommerce_last_modified'];
			}
			//otherwise dont set last_modified (we want the current value preserved)
		}
		else
		{ //existing
			$item_data['last_modified'] = date('Y-m-d H:i:s');
		}
			
		if (!$item_id or !$this->exists($item_id))
		{
			if($this->db->insert('items',$item_data))
			{
				$item_data['item_id']=$this->db->insert_id();
				
				if(isset($item_data['unit_price']) || isset($item_data['cost_price']))
				{
					$this->save_price_history($item_data['item_id'],NULL,NULL,isset($item_data['unit_price']) ? $item_data['unit_price'] : NULL,isset($item_data['cost_price']) ? $item_data['cost_price'] : NULL, TRUE);
				}
				return true;
			}
			return false;
		}

		if (isset($item_data['unit_price']) || isset($item_data['cost_price']))
		{
			$this->save_price_history($item_id,NULL,NULL,isset($item_data['unit_price']) ? $item_data['unit_price'] : NULL,isset($item_data['cost_price']) ? $item_data['cost_price'] : NULL);
		}

		$this->db->where('item_id', $item_id);
		return $this->db->update('items',$item_data);
	}

	function get_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('item_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}
	
	public function get_item_ids_for_search()
	{
		$item_ids = array();
		
		if ($this->is_empty_search())
		{				
			foreach($this->get_all()->result_array() as $row)
			{
				$item_ids[] = $row['item_id'];
			}
		}
		else
		{
			$total_items = $this->count_all();
			$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
			$result = $this->search(isset($params['search']) ? $params['search'] : '',$params['deleted'] ? $params['deleted'] : 0, isset($params['category_id']) ? $params['category_id'] : '',$total_items,0,'name','asc', isset($params['fields']) ? $params['fields']: 'all');
		
			foreach($result->result() as $row)
			{
				$item_ids[] = $row->item_id;
			}
		}
		
		return $item_ids;
	}
	/*
	Updates multiple items at once
	*/
	function update_multiple($item_data,$item_ids,$select_inventory=0)
	{
		if($select_inventory)
		{
			if ($this->is_empty_search())
			{				
				return $this->db->update('items',$item_data);
			}
			else
			{
				$item_ids = array();
				
				$total_items = $this->count_all();
				$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
				$result = $this->search(isset($params['search']) ? $params['search'] : '',$params['deleted'] ? $params['deleted'] : 0, isset($params['category_id']) ? $params['category_id'] : '',$total_items,0,'name','asc', isset($params['fields']) ? $params['fields']: 'all');
			
				foreach($result->result() as $row)
				{
					$item_ids[] = $row->item_id;
				}
				$this->load->helper('database');
				return create_and_execute_large_update_query_items($item_ids, $item_data);
			}
		}
		else
		{
			$this->load->helper('database');
			return create_and_execute_large_update_query_items($item_ids, $item_data);
		}
	}
	
	function update_multiple_percent($item_ids,$select_inventory=0,$cost_price_percent = FALSE, $unit_price_percent = FALSE, $promo_price_percent = FALSE, $promo_price_use_selling_price = FALSE)
	{
		if($select_inventory)
		{
			if ($this->is_empty_search())
			{				
				if ($cost_price_percent)
				{
					$this->db->set('cost_price',"cost_price * (1+($cost_price_percent/100))", FALSE);
				}
			
				if ($unit_price_percent)
				{
					$this->db->set('unit_price',"unit_price * (1+($unit_price_percent/100))", FALSE);
				}
			
				if ($promo_price_percent)
				{
					if ($promo_price_use_selling_price)
					{
						$this->db->set('promo_price',"unit_price * (1+($promo_price_percent/100))", FALSE);
					}
					else
					{
						$this->db->set('promo_price',"promo_price * (1+($promo_price_percent/100))", FALSE);						
					}
				}
				
				return $this->db->update('items');
			}
			else
			{
				$item_ids = array();
				
				$total_items = $this->count_all();
				$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
				$result = $this->search(isset($params['search']) ? $params['search'] : '',$params['deleted'] ? $params['deleted'] : 0, isset($params['category_id']) ? $params['category_id'] : '',$total_items,0,'name','asc', isset($params['fields']) ? $params['fields']: 'all');
			
				foreach($result->result() as $row)
				{
					$item_ids[] = $row->item_id;
				}
				$this->load->helper('database');
				return create_and_execute_large_update_query_location_items_percent($item_ids, $cost_price_percent,$unit_price_percent,$promo_price_percent,$promo_price_percent);
			}
		}
		else
		{
			$this->load->helper('database');
			return create_and_execute_large_update_query_location_items_percent($item_ids, $cost_price_percent,$unit_price_percent,$promo_price_percent);
		}
	}
	
	function update_tiers($item_ids,$select_inventory, $tier_types, $tier_values)
	{
		if (!$tier_types)
		{
			$tier_types = array();
		}
		
		if (!$tier_values)
		{
			$tier_values = array();
		}
		if($select_inventory)
		{
			$item_ids = array();
				
			$total_items = $this->count_all();
			$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
			$result = $this->search(isset($params['search']) ? $params['search'] : '',$params['deleted'] ? $params['deleted'] : 0,isset($params['category_id']) ? $params['category_id'] : '',$total_items,0,'name','asc', isset($params['fields']) ? $params['fields']: 'all');
		
			foreach($result->result() as $row)
			{
				$item_ids[] = $row->item_id;
			}
								
		}
		
		foreach($item_ids as $item_id)
		{
			//Save price tiers
			foreach($tier_types as $tier_id=>$tier_type_value)
			{			
				$tier_data=array('tier_id'=>$tier_id);
				$tier_data['item_id'] = $item_id;							
				$tier_value = $tier_values[$tier_id];
					
				if ($tier_value !== '')
				{										
					if ($tier_type_value == 'unit_price')
					{
						$tier_data['unit_price'] = (float)$tier_value;
						$tier_data['percent_off'] = NULL;
						$tier_data['cost_plus_percent'] = NULL;
						$tier_data['cost_plus_fixed_amount'] = NULL;
					}
					elseif($tier_type_value == 'percent_off')
					{
						$tier_data['percent_off'] = (float)$tier_value;
						$tier_data['unit_price'] = NULL;
						$tier_data['cost_plus_percent'] = NULL;
						$tier_data['cost_plus_fixed_amount'] = NULL;
					}
					elseif($tier_type_value == 'cost_plus_percent')
					{
						$tier_data['percent_off'] = NULL;
						$tier_data['unit_price'] = NULL;
						$tier_data['cost_plus_percent'] = (float)$tier_value;
						$tier_data['cost_plus_fixed_amount'] = NULL;
					}
					elseif($tier_type_value == 'cost_plus_fixed_amount')
					{
						$tier_data['percent_off'] = NULL;
						$tier_data['unit_price'] = NULL;
						$tier_data['cost_plus_percent'] = NULL;
						$tier_data['cost_plus_fixed_amount'] = (float)$tier_value;
					}
										
					$this->Item->save_item_tiers($tier_data,$item_id);
				}			
			}
		}
	}
	
	function is_empty_search()
	{
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all');
		
		if (!$params['search'] && !$params['category_id'])
		{
			return TRUE;
		}
		
		return FALSE;
	}
	

	function delete($item_id)
	{
		$this->db->where('item_id', $item_id);
		return $this->db->update('items', array('deleted' => 1));
	}

	/*
	Deletes a list of items
	*/
	function delete_list($item_ids, $select_inventory)
	{		
		if($select_inventory)
		{
			if ($this->is_empty_search())
			{	
				return $this->db->update('items', array('deleted' => 1, 'last_modified' => date('Y-m-d H:i:s')));
			}
			else
			{
				$item_ids = array();
				$total_items = $this->count_all();
			
				$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
				$result = $this->search(isset($params['search']) ? $params['search'] : '',$params['deleted'] ? $params['deleted'] : 0, isset($params['category_id']) ? $params['category_id'] : '',$total_items,0,'name','asc', isset($params['fields']) ? $params['fields']: 'all');
			
				foreach($result->result() as $row)
				{
					$item_ids[] = $row->item_id;
				}
							
				$this->load->helper('database');
				if(create_and_execute_large_update_query_items($item_ids, array('deleted' => 1, 'last_modified' => date('Y-m-d H:i:s'))))
				{
					return $item_ids;
				} 
				
				return false;
			}
		}
		else
		{
			
			$this->load->helper('database');	
			if(create_and_execute_large_update_query_items($item_ids, array('deleted' => 1, 'last_modified' => date('Y-m-d H:i:s'))))
			{
				return $item_ids;
			}
			
			return false;
		}
 	}

	/*
	undeletes a list of items
	*/
	function undelete_list($item_ids,$select_inventory)
	{		
		if($select_inventory)
		{
			if ($this->is_empty_search())
			{	
				return $this->db->update('items', array('deleted' => 0, 'last_modified' => date('Y-m-d H:i:s')));
			}
			else
			{
				$item_ids = array();
				$total_items = $this->count_all();
			
				$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 1);
				$result = $this->search(isset($params['search']) ? $params['search'] : '',$params['deleted'] ? $params['deleted'] : 1, isset($params['category_id']) ? $params['category_id'] : '',$total_items,0,'name','asc', isset($params['fields']) ? $params['fields']: 'all');
			
				foreach($result->result() as $row)
				{
					$item_ids[] = $row->item_id;
				}
							
				$this->load->helper('database');
				if(create_and_execute_large_update_query_items($item_ids, array('deleted' => 0, 'last_modified' => date('Y-m-d H:i:s'))))
				{
					return $item_ids;
				}
				
				return false;
			}
		}
		else
		{
			$this->load->helper('database');
			if(create_and_execute_large_update_query_items($item_ids, array('deleted' => 0, 'last_modified' => date('Y-m-d H:i:s'))))
			{
				return $item_ids;
			}
			
			return false;
		}
 	}

  /*
    Taking a suggestions array, add all variation data
  */
  function add_variation_data(&$suggestions, $search, $price_field)
  {
    $variation_ids_to_lookup = array();
		// remove all suggestions without a label
		for($k=count($suggestions)-1;$k>=0;$k--)
		{
			if (!$suggestions[$k]['label'])
			{
				unset($suggestions[$k]);
			}
		}

		$suggestions = array_values($suggestions);

		// Add variations
		foreach($suggestions as $suggestion)
		{
			$variation_ids_to_lookup[] = $suggestion['variation_id'];
		}

		$variation_ids_to_lookup = array_filter(array_unique($variation_ids_to_lookup));

		$this->load->model('Item_variations');

		$variations = $this->Item_variations->get_multiple_info($variation_ids_to_lookup);
		$attributes = $this->Item_variations->get_attributes($variation_ids_to_lookup);

		foreach($suggestions as &$suggestion)
		{
			if(isset($variations[$suggestion['variation_id']]))
			{
				if(isset($variations[$suggestion['variation_id']]['variation_name']) && $variations[$suggestion['variation_id']]['variation_name'])
				{
					$suggestion['attributes'] = $variations[$suggestion['variation_id']]['variation_name'];
				}
				else
				{
					$suggestion['attributes'] = implode(', ', array_column($attributes[$suggestion['variation_id']],'label'));
				}

				if(isset($variations[$suggestion['variation_id']][$price_field]) && $variations[$suggestion['variation_id']][$price_field])
				{
					$price_start = strrpos($suggestion['label'],' - ');
					$search = substr($suggestion['label'],$price_start);
					$replace = ' - '.to_currency($variations[$suggestion['variation_id']][$price_field]);
					$suggestion['label']= str_replace($search,$replace,$suggestion['label']);
				}
			}

			unset($suggestion['variation_id']);
		}

  } // add_variation_data


 	/*
	Check if unique number of elements in array exceeds the $limit, slice the array down to limit
	*/
  function is_array_full(&$array, $limit)
  {
    $array = array_map("unserialize", array_unique(array_map("serialize", $array)));
    if(count($array) > $limit)
    {
      $array = array_slice($array, 0, $limit);
    }
    return count( $array ) >= $limit;
  } // is_array_full
 	/*
	Get search suggestions to find items on the manage screen
	*/
	function get_item_search_suggestions_without_variations($search,$deleted = 0,$limit=25,$price_field = FALSE,$hide_inactive = false)
	{
		if (!trim($search))
		{
			return array();
		}
		
		if ($price_field == 'cost_price')
		{
			$has_cost_price_permission = $this->Employee->has_module_action_permission('items','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
			
			if (!$has_cost_price_permission)
			{
				$price_field = FALSE;
			}
		}
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$suggestions = array();

		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->get_custom_field($k)) 
			{
				$this->load->helper('date');
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$this->db->select('custom_field_'.$k.'_value as custom_field,items.main_image_id as image_id, items.*, categories.name as category', false);						
				}
				else
				{
					$this->db->select('FROM_UNIXTIME(custom_field_'.$k.'_value, "'.get_mysql_date_format().'") as custom_field,items.main_image_id as image_id, items.*, categories.name as category', false);
				}
				
				$this->db->join('categories', 'categories.id = items.category_id','left');
				
				$this->db->from('items');
				$this->db->where('items.deleted',$deleted);
				if ($hide_inactive)
				{
					$this->db->where('item_inactive',0);
				}
			
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$this->db->like("custom_field_${k}_value",$search,'after');
				}
				else
				{
					$this->db->where("custom_field_${k}_value IS NOT NULL and custom_field_${k}_value != 0 and FROM_UNIXTIME(custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search))), NULL, false);					
				}
				$this->db->limit($limit);
				$by_custom_field = $this->db->get();
	
				$temp_suggestions = array();
	
				foreach($by_custom_field->result() as $row)
				{
					$data = array(
						'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
						'category' => $row->category,
						'item_number' => $row->item_number,
					);
					$data['label'] = $row->custom_field.($price_field ? ' - '.to_currency($row->$price_field) : '');
					
					$key = $row->item_id;
					$temp_suggestions[$key] = $data;

				}
		
				foreach($temp_suggestions as $key => $value)
				{
					$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'avatar' => $value['avatar'], 'subtitle' => '');		
				}
				
				$suggestions = $this->filter_location_item($suggestions);
				if ( $this->Item->is_array_full($suggestions, $limit) )
				{
					return $suggestions;
				}
			}			
		}

		if($this->config->item('supports_full_text') && $this->config->item('enhanced_search_method'))
		{
			if ($price_field) {
			  $this->db->select('items.'. $price_field);
			}
			$this->db->select("items.item_id, item_number, items.main_image_id as image_id, items.name, categories.name as category, MATCH (".$this->db->dbprefix('items').".name) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE) as rel, CASE WHEN ".$this->db->dbprefix('items').".name = ".$this->db->escape($search)." THEN 1 ELSE 0 END AS exact_score", false);
			$this->db->from('items');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where("MATCH (".$this->db->dbprefix('items').".name) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE)", NULL, FALSE);			
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$this->db->order_by('rel DESC, exact_score DESC');
			$by_name = $this->db->get();
		
			$temp_suggestions = array();
			foreach($by_name->result() as $row)
			{
				$data = array(
						'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
						'category' => $row->category,
						'item_number' => $row->item_number,
					);

				$data['label'] = $row->name . ($price_field ? ' - '.to_currency($row->$price_field) : '');
				$temp_suggestions[$row->item_id] = $data;				
			}
		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'avatar' => $value['avatar'], 'subtitle' => $value['category'] ? $value['category'] : lang('common_none'));		
			}
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}

			// Query 2: Status: Optimised and cleaned
			$this->db->protect_identifiers('straight_join');        
			$this->db->select('straight_join 1 as _hacked', false); 
			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select('items.item_id, items.main_image_id as image_id, items.name, categories.name as category'); 
			$this->db->from('categories');
			$this->db->like('categories.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->join('items', 'items.category_id=categories.id');
			$this->db->where('items.deleted',$deleted);

			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$by_category = $this->db->get();

			$temp_suggestions = array();
			foreach($by_category->result() as $row)
			{
				$data = array(
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'label' => $row->name.($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'category' => $row->category,
				);
				$temp_suggestions[$row->item_id] = $data;
			}
		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'avatar' => $value['avatar'], 'subtitle' => $value['category'] ? $value['category'] : lang('common_none'));		
			}
		
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}

			// Query 3:
			$this->db->select("items.unit_price,items.cost_price,item_number,items.item_id,items.main_image_id as image_id,items.name, categories.name as category, size, MATCH (item_number) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE) as rel, CASE WHEN ".$this->db->dbprefix('items').".item_number = ".$this->db->escape($search)." THEN 1 ELSE 0 END AS exact_score", false);
			$this->db->from('items');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where("MATCH (item_number) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE)", NULL, FALSE);			
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$this->db->order_by('rel DESC, exact_score DESC');
			
			$by_item_number = $this->db->get();
		
			$temp_suggestions = array();
			foreach($by_item_number->result() as $row)
			{
				$data = array(
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'label' => $row->item_number.($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'category' => $row->category);

				$temp_suggestions[$row->item_id] = $data;

			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'avatar' => $value['avatar'], 'subtitle' => $value['category'] ? $value['category'] : lang('common_none'));		
			}
			
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}

			// Query 4:
			$this->db->select("items.unit_price,items.cost_price,item_number,items.item_id,product_id,items.main_image_id as image_id,items.name, categories.name as category, size, MATCH (item_number) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE) as rel, CASE WHEN ".$this->db->dbprefix('items').".product_id = ".$this->db->escape($search)." THEN 1 ELSE 0 END AS exact_score", false);
			$this->db->from('items');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where("MATCH (product_id) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE)", NULL, FALSE);			
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->limit($limit);
			$this->db->order_by('rel DESC, exact_score DESC');
			$this->db->group_by('items.item_id');
			$by_product_id = $this->db->get();
			$temp_suggestions = array();
			foreach($by_product_id->result() as $row)
			{
					$data = array(
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'label' => $row->product_id.($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'category' => $row->category);

				$temp_suggestions[$row->item_id] = $data;

			}
			
		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'avatar' => $value['avatar'], 'subtitle' => $value['category'] ? $value['category'] : lang('common_none'));		
			}
		
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}

			// Query 5:
			$this->db->select("items.unit_price,items.cost_price,items.item_id,items.main_image_id as image_id,items.name, categories.name as category, size, MATCH (item_number) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE) as rel", false);
			$this->db->from('items');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where('items.item_id', $search);
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->limit($limit);
			$this->db->order_by('rel DESC');
			$this->db->group_by('items.item_id');
			$by_item_id = $this->db->get();
			$temp_suggestions = array();
			foreach($by_item_id->result() as $row)
			{
				$data = array(
				'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
				'label' => $row->item_id.($price_field ? ' - '.to_currency($row->$price_field) : ''),
				'category' => $row->category);

				$temp_suggestions[$row->item_id] = $data;

			}

		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'avatar' => $value['avatar'], 'subtitle' => $value['category'] ? $value['category'] : lang('common_none'));		
			}
			
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}


			// Query 6:
			$this->db->select('items.unit_price,items.cost_price,items.item_id,items.main_image_id as image_id, name, additional_item_numbers.item_number');
			$this->db->from('additional_item_numbers');
			$this->db->join('items', 'items.item_id = additional_item_numbers.item_id','left');

			$this->db->like('additional_item_numbers.item_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$by_additional_item_numbers = $this->db->get();
			
			$temp_suggestions = array();
			foreach($by_additional_item_numbers->result() as $row)
			{
				$data = array(
				'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
				'label' => $row->name.($price_field ? ' - '.to_currency($row->$price_field) : ''),
				'category' => $row->item_number);

				$temp_suggestions[$row->item_id] = $data;

			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'avatar' => $value['avatar'], 'subtitle' => $value['category'] ? $value['category'] : lang('common_none'));		
			}
		
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}

			// Query 7:
			$this->db->select('items.unit_price,items.cost_price,items.item_id, items.name as item_name, items.main_image_id as image_id, tags.name as tag_name');
			$this->db->from('items_tags');
			$this->db->join('tags', 'items_tags.tag_id=tags.id');
			$this->db->join('items', 'items_tags.item_id=items.item_id');
			$this->db->like('tags.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
		
			$by_tags = $this->db->get();
			$temp_suggestions = array();
		
			foreach($by_tags->result() as $row)
			{
				$data = array(
				'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
				'label' => $row->tag_name.($price_field ? ' - '.to_currency($row->$price_field) : ''),
				'category' => $row->tag_name);

				$temp_suggestions[$row->item_id] = $data;

			}
		
		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'avatar' => $value['avatar'], 'subtitle' => $value['category'] ? $value['category'] : lang('common_none'));		
			}
			
		}
		else
		{
			
			// Query 1:
			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select('items.item_id, items.name, items.main_image_id as image_id, categories.name as category');
			$this->db->from('items');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->like('items.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');				

			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$by_name = $this->db->get();
		
			$temp_suggestions = array();

			foreach($by_name->result() as $row)
			{
				$data = array(
					'name' => $row->name . ($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'subtitle' => $row->category,
					'avatar' => $row->image_id ? app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					 );
				$temp_suggestions[$row->item_id] = $data;
			}
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}

			// Query 2:
			$this->db->protect_identifiers('straight_join');        
			$this->db->select('straight_join 1 as _hacked', false); 

			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select('items.name as item_name,items.item_id, items.main_image_id as image_id, categories.name as category');
			$this->db->from('categories');
			$this->db->like('categories.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->join('items', 'items.category_id=categories.id');
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}

			$this->db->limit($limit);
			$this->db->group_by('items.item_id');
			$by_category = $this->db->get();
		
			$temp_suggestions = array();
			foreach($by_category->result() as $row)
			{
				$data = array(
					'name' => $row->item_name . ($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'subtitle' => $row->category,
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					 );
				$temp_suggestions[$row->item_id] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}
		
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}

			// Query 3:
			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select('items.item_id, items.item_number, items.main_image_id as image_id, categories.name as category');
			$this->db->from('items');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->like('item_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);

			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$by_item_number = $this->db->get();
		
			$temp_suggestions = array();
			foreach($by_item_number->result() as $row)
			{
				$data = array(
					'name' => $row->item_number . ($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'subtitle' => $row->category,
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					 );
				$temp_suggestions[$row->item_id] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}
			// Query 4:
			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select('items.item_id, items.product_id, items.main_image_id as image_id, categories.name as category');
			$this->db->from('items');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->like('product_id', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$by_product_id = $this->db->get();
			$temp_suggestions = array();
			foreach($by_product_id->result() as $row)
			{
				$data = array(
					'name' => $row->product_id.($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'subtitle' => $row->category,
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					 );
				$temp_suggestions[$row->item_id] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}
			// Query 5:
			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select('items.item_id, items.main_image_id as image_id, categories.name as category');
			$this->db->from('items');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where('items.item_id', $search);
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$by_item_id = $this->db->get();
			$temp_suggestions = array();
			foreach($by_item_id->result() as $row)
			{
				$data = array(
					'name' => $row->item_id.($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'subtitle' => $row->category,
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					 );
				$temp_suggestions[$row->item_id] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}
			
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}
			// Query 6:
			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select('items.item_id, items.main_image_id as image_id, items.name, additional_item_numbers.item_number');
			$this->db->from('additional_item_numbers');
			$this->db->join('items', 'items.item_id = additional_item_numbers.item_id','left');
			$this->db->like('additional_item_numbers.item_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
			$by_additional_item_numbers = $this->db->get();
			$temp_suggestions = array();
			
			foreach($by_additional_item_numbers->result() as $row)
			{
				$data = array(
					'name' => $row->item_number.($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'subtitle' => $row->name,
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					 );
				$temp_suggestions[$row->item_id] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}
			
			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				return $suggestions;
			}

			// Query 7:
			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select('items.item_id, items.name as item_name, items.main_image_id as image_id, tags.name as tag_name,items.unit_price,items.cost_price');
			$this->db->from('items_tags');
			$this->db->join('tags', 'items_tags.tag_id=tags.id');
			$this->db->join('items', 'items_tags.item_id=items.item_id');
			$this->db->like('tags.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->group_by('items.item_id');
			$this->db->limit($limit);
		
			$by_tags = $this->db->get();
			$temp_suggestions = array();
		
			foreach($by_tags->result() as $row)
			{
				$data = array(
					'name' => $row->item_name.($price_field ? ' - '.to_currency($row->$price_field) : ''),
					'subtitle' => $row->tag_name,
					'avatar' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					 );
				$temp_suggestions[$row->item_id] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle'] ? $value['subtitle'] : lang('common_none'));		
			}			
		}

		$suggestions = $this->filter_location_item($suggestions);
		return $suggestions;
	}
	
	function check_duplicate($term)
	{
		$this->db->from('items');
		$this->db->where('deleted',0);		
		$query = $this->db->where("name = ".$this->db->escape($term));
		$query=$this->db->get();
		
		if($query->num_rows()>0)
		{
			return true;
		}
	}

	function get_item_search_suggestions($search,$deleted=0,$price_field = 'unit_price',$limit=25,$hide_inactive = false)
	{
    $query = array(); 	

		if (!trim($search))
		{
			return array();
		}
		
		if ($price_field == 'cost_price')
		{
			$has_cost_price_permission = $this->Employee->has_module_action_permission('items','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
			
			if (!$has_cost_price_permission)
			{
				$price_field = FALSE;
			}
		}
		
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$phppos_location_items = $this->db->dbprefix('location_items');
		$phppos_location_item_variations = $this->db->dbprefix('location_item_variations');
		$phppos_item_variations = $this->db->dbprefix('item_variations');
		
		
		$suggestions = array();
		$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;

		$items_table = $this->db->dbprefix('items');
		$item_images_table = $this->db->dbprefix('item_images');
		$item_variations_table = $this->db->dbprefix('item_variations');

		// Query 0: Custom Fields
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->get_custom_field($k)) 
			{
				$this->load->helper('date');
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$this->db->select('location_items.quantity as quantity, custom_field_'.$k.'_value as custom_field, item_variations.deleted as variation_deleted,items.main_image_id as image_id, items.*, item_variations.id as item_variation_id, categories.name as category', false);						
				}
				else
				{
					$this->db->select('location_items.quantity as quantity,FROM_UNIXTIME(custom_field_'.$k.'_value, "'.get_mysql_date_format().'") as custom_field, item_variations.deleted as variation_deleted,items.main_image_id as image_id, items.*, item_variations.id as item_variation_id, categories.name as category', false);
				}
				$this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
				$this->db->join('categories', 'categories.id = items.category_id','left');
				
				$this->db->from('items');
				$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
				$this->db->where('items.deleted',$deleted);
			
				if ($hide_inactive)
				{
					$this->db->where('item_inactive',0);
				}
				
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$this->db->like("custom_field_${k}_value",$search,'after');
				}
				else
				{
					$this->db->where("custom_field_${k}_value IS NOT NULL and custom_field_${k}_value != 0 and FROM_UNIXTIME(custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search))), NULL, false);					
				}
				$this->db->limit($limit);
				$by_custom_field = $this->db->get();
	
				$temp_suggestions = array();
	
				foreach($by_custom_field->result() as $row)
				{
					$data = array(
						'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
						'category' => $row->category,
						'quantity' => $row->quantity,
						'item_number' => $row->item_number,
						'variation_id' => $row->variation_deleted ? NULL : $row->item_variation_id,
					);
					$data['label'] = $row->name. ': '.$row->custom_field.' - '.($price_field ? to_currency($row->$price_field) : '');
					
					$key = $row->item_variation_id ? $row->item_id .($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
					$temp_suggestions[$key] = $data;

				}
		
				foreach($temp_suggestions as $key => $value)
				{
					$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
				}

				$suggestions = $this->filter_location_item($suggestions);

				if ( $this->Item->is_array_full($suggestions, $limit) )
				{
					$this->Item->add_variation_data($suggestions, $search, $price_field);
					return $suggestions;
				}

			}			
		}
		
		if (!$this->config->item('speed_up_search_queries'))
		{	
			$quantity_field ="IF(SUM(if($phppos_item_variations.deleted=0,1, 0))  > 0,SUM(IF($phppos_item_variations.deleted=0, $phppos_location_item_variations.quantity, 0)),$phppos_location_items.quantity)";
		}
		else
		{
			$quantity_field = 'NULL';
		}
		
	
		
		if($this->config->item('supports_full_text') && $this->config->item('enhanced_search_method'))
		{
		  // Query 1:
      $this->db->select('items.item_id, items.item_number, items.size, items.name'); // removed items.*
      if ($price_field) {
        $this->db->select('items.'. $price_field); 
      }
			$this->db->select("$quantity_field as quantity,item_variations.deleted as variation_deleted,items.main_image_id as image_id, item_variations.id as item_variation_id, categories.name as category, MATCH (".$this->db->dbprefix('items').".name) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE) as rel, CASE WHEN ".$this->db->dbprefix('items').".name = ".$this->db->escape($search)." THEN 1 ELSE 0 END AS exact_score", false);
			$this->db->from('items');
			$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
			$this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
			$this->db->join('location_item_variations', "`$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location",'left');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where('items.deleted',$deleted);
			$this->db->where('items.system_item',0);
			
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->group_start();
			$this->db->where("MATCH (".$this->db->dbprefix('items').".name) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE)", NULL, FALSE);			
			$this->db->or_where("MATCH (".$this->db->dbprefix('item_variations').".name) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE)", NULL, FALSE);			
			$this->db->group_end();
			$this->db->limit($limit);
			$this->db->order_by('rel DESC, exact_score DESC');
			$this->db->group_by('items.item_id, item_variations.id');
			$by_name = $this->db->get();
				
			$temp_suggestions = array();
		
			foreach($by_name->result() as $row)
			{	
				$data = array(
					'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'category' => $row->category,
					'quantity' => $row->quantity,
					'item_number' => $row->item_number,
					'variation_id' => $row->variation_deleted ? NULL : $row->item_variation_id,
				);
				
				$key = $row->item_variation_id ? $row->item_id .($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
			
				if ($row->category && $row->size)
				{
					$data['label'] = $row->name . ' ('.$row->category.', '.$row->size.') - '.($price_field ? to_currency($row->$price_field) : '');

					$temp_suggestions[$key] = $data;
				}
				elseif ($row->category)
				{
					$data['label'] = $row->name . ' ('.$row->category.') - '.($price_field ? to_currency($row->$price_field) : '');

					$temp_suggestions[$key] =  $data;
				}
				elseif ($row->size)
				{
					$data['label'] = $row->name . ' ('.$row->size.') - '.($price_field ? to_currency($row->$price_field) : '');

					$temp_suggestions[$key] =  $data;
				}
				else
				{
					$data['label'] = $row->name. ' - '.($price_field ? to_currency($row->$price_field) : '');
										
					$temp_suggestions[$key] = $data;				
				}
			
			}
		
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
			}

			$suggestions = $this->filter_location_item($suggestions);
			
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				$this->Item->add_variation_data($suggestions, $search, $price_field);
				return $suggestions;
			}

      // Query 2:
      $this->db->select('items.item_id, items.item_number, items.size, items.name'); // removed items.*
      if ($price_field) {
        $this->db->select('items.'. $price_field); 
      }
			$this->db->select("$quantity_field as quantity,item_variations.item_number as item_variation_number,item_variations.deleted as variation_deleted,items.main_image_id as image_id,item_variations.id as item_variation_id, categories.name as category, MATCH (".$this->db->dbprefix('items').".item_number) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE) as rel, CASE WHEN ".$this->db->dbprefix('items').".item_number = ".$this->db->escape($search)." THEN 1 ELSE 0 END AS exact_score", false);
			$this->db->from('items');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
			}
			
			$this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_item_variations', "`$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location",'left');
			}
			
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where('items.deleted',$deleted);
			$this->db->where('items.system_item',0);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			$this->db->group_start();
			$this->db->where("MATCH (".$this->db->dbprefix('items').".item_number) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE)", NULL, FALSE);
			$this->db->or_like($this->db->dbprefix('item_variations').'.item_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->group_end();
			$this->db->limit($limit);
			$this->db->order_by('rel DESC, exact_score DESC');
			$this->db->group_by('items.item_id, item_variations.id');
			$by_item_number = $this->db->get();

			$temp_suggestions = array();

			foreach($by_item_number->result() as $row)
			{
				$data = array(
					'label' => $row->item_variation_number ? $row->item_variation_number : $row->item_number.' ('.$row->name.') - '.($price_field ? to_currency($row->$price_field) : ''),
					'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'category' => $row->category,
					'quantity' => $row->quantity,
					'item_number' => $row->item_number,
					'variation_id' => $row->variation_deleted ? $row->item_variation_id : NULL,
				);
				
				$key = $row->item_variation_id ? $row->item_id .($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
				
				$temp_suggestions[$key] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				$this->Item->add_variation_data($suggestions, $search, $price_field);
				return $suggestions;
			}

      // Query 3
      $this->db->select('items.item_id, items.item_number, items.size, items.name, items.product_id'); // removed items.*
      if ($price_field) {
        $this->db->select('items.'. $price_field);
      }
			$this->db->select("$quantity_field as quantity,item_variations.deleted as variation_deleted,items.main_image_id as image_id, item_variations.id as item_variation_id, categories.name as category,MATCH (product_id) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE) as rel, CASE WHEN ".$this->db->dbprefix('items').".product_id = ".$this->db->escape($search)." THEN 1 ELSE 0 END AS exact_score", false);
			$this->db->from('items');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
			}
			$this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_item_variations', "`$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location",'left');
			}
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where("MATCH (product_id) AGAINST (".$this->db->escape(escape_full_text_boolean_search($search))." IN BOOLEAN MODE)", NULL, FALSE);
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->group_by('items.item_id, item_variations.id');
			$this->db->limit($limit);
			$this->db->order_by('rel DESC, exact_score DESC');

			$by_product_id = $this->db->get();

			$temp_suggestions = array();

			foreach($by_product_id->result() as $row)
			{
				$data = array(
					'label' => $row->product_id.' ('.$row->name.') - '.($price_field ? to_currency($row->$price_field) : ''),
					'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'category' => $row->category,
					'quantity' => $row->quantity,
					'item_number' => $row->item_number,
					'variation_id' => $row->variation_deleted ? NULL : $row->item_variation_id,
				);
				
				$key = $row->item_variation_id ? $row->item_id .($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
				
				$temp_suggestions[$key] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
			}


			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				$this->Item->add_variation_data($suggestions, $search, $price_field);
				return $suggestions;
			}

      // Query 4
			$this->db->select("$quantity_field as quantity,items.main_image_id as image_id, item_variations.id as item_variation_id, additional_item_numbers.*, items.unit_price, items.cost_price, categories.name as category", false);
			$this->db->from('additional_item_numbers');
			$this->db->join('items', 'additional_item_numbers.item_id = items.item_id');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
			}
			$this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_item_variations', "`$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location",'left');
			}
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->like($this->db->dbprefix('additional_item_numbers').'.item_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->group_by('items.item_id, item_variations.id');
			$this->db->limit($limit);
			$by_additional_item_numbers = $this->db->get();

			$temp_suggestions = array();
			foreach($by_additional_item_numbers->result() as $row)
			{
				$data = array(
					'label' => $row->item_number.' - '.($price_field ? to_currency($row->$price_field) : ''),
					'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'category' => $row->category,
					'quantity' => $row->quantity,
					'item_number' => $row->item_number,
					'variation_id' => $row->item_variation_id,
				);
				
				$key = $row->item_variation_id ? $row->item_id .($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
				
				$temp_suggestions[$key] = $data;
			}

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				$this->Item->add_variation_data($suggestions, $search, $price_field);
				return $suggestions;
			}

		}
		else
		{
			// Query 1
			// Note: When the speedup search is enabled it will use old code which runs fine (after cleanup)
			if ($this->config->item('speed_up_search_queries'))
			{
			  // OLD QUERY: Exec 3000ms+
        $this->db->select('items.item_id, items.item_number, items.size, items.name'); // removed items.*
        if ($price_field) {
          $this->db->select('items.'. $price_field);
        }

        $this->db->select("$quantity_field as quantity, item_variations.deleted as variation_deleted, item_variations.id as item_variation_id, items.main_image_id as image_id, categories.name as category", false);
        $this->db->from('items');

        if (!$this->config->item('speed_up_search_queries'))
        {
          $this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
        }

        $this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
        if (!$this->config->item('speed_up_search_queries'))
        {
          $this->db->join('location_item_variations', "`$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location",'left');
        }

        $this->db->join('categories', 'categories.id = items.category_id','left');
        $this->db->where('items.deleted',$deleted);
        $this->db->where('items.system_item',0);
        if ($hide_inactive)
        {
          $this->db->where('item_inactive',0);
        }

        $this->db->group_by('items.item_id, item_variations.id');
        $this->db->group_start();
        $this->db->like($this->db->dbprefix('items').'.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
        $this->db->or_like($this->db->dbprefix('item_variations').'.name', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
        $this->db->group_end();
        $this->db->limit($limit);
        $by_name = $this->db->get();
      } else {
      $hide_inactive_sql_snippet = $hide_inactive? 'AND `item_inactive` = 0' : '';
      $sql = <<<SQL
select
	ta.*
      ,IF(SUM(IF(variation_deleted = 0, 1, 0)) > 0,
         SUM(IF(variation_deleted = 0, phppos_location_item_variations.quantity, 0)),
         phppos_location_items.quantity) AS quantity
from
	(select
		phppos_items.item_id, phppos_item_variations.id AS item_variation_id,
        phppos_item_variations.deleted AS variation_deleted,
		phppos_items.item_number,
		phppos_items.unit_price, phppos_items.cost_price,
		phppos_items.size,phppos_items.name,
		phppos_items.main_image_id as image_id,
		phppos_categories.name AS category
	from `phppos_items`
    LEFT JOIN `phppos_item_variations` ON `phppos_items`.`item_id` = `phppos_item_variations`.`item_id`
    LEFT JOIN `phppos_categories` ON `phppos_categories`.`id` = `phppos_items`.`category_id`
	WHERE
      `phppos_items`.`deleted` = 0
			AND `phppos_items`.`system_item` = 0
			$hide_inactive_sql_snippet
			AND (`phppos_items`.`name` LIKE ? ESCAPE '!' OR `phppos_item_variations`.`name` LIKE ? ESCAPE '!')
	 order by phppos_items.item_id, phppos_item_variations.id
    ) ta
LEFT JOIN `phppos_location_item_variations` ON `phppos_location_item_variations`.`item_variation_id` =  ta.item_variation_id AND `phppos_location_item_variations`.`location_id` = $current_location
LEFT JOIN `phppos_location_items` ON `phppos_location_items`.`item_id` = ta.item_id AND `phppos_location_items`.`location_id` = $current_location
GROUP BY ta.item_id, ta.item_variation_id
LIMIT ?
SQL;

        $wrap_like = $this->config->item('speed_up_search_queries') ? $search.'%' : '%'.$search.'%';
        $by_name = $this->db->query($sql, array($wrap_like,$wrap_like, $limit));
      }


			$temp_suggestions = array();
			
			foreach($by_name->result() as $row)
			{
				$data = array(
					'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'category' => $row->category,
					'quantity' => $row->quantity,
					'item_number' => $row->item_number,
					'variation_id' => $row->variation_deleted ? NULL : $row->item_variation_id
				);
					
				$key = $row->item_variation_id ? $row->item_id .($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
				
				if ($row->category && $row->size)
				{
					$data['label'] = $row->name . ' ('.$row->category.', '.$row->size.') - '.($price_field ? to_currency($row->$price_field) : '');

					$temp_suggestions[$key] = $data;
				}
				elseif ($row->category)
				{
					$data['label'] = $row->name . ' ('.$row->category.') - '.($price_field ? to_currency($row->$price_field) : '');

					$temp_suggestions[$key] =  $data;
				}
				elseif ($row->size)
				{
					$data['label'] = $row->name . ' ('.$row->size.') - '.($price_field ? to_currency($row->$price_field) : '');

					$temp_suggestions[$key] =  $data;
				}
				else
				{
					$data['label'] = $row->name.' - '.($price_field ? to_currency($row->$price_field) : '');

					$temp_suggestions[$key] = $data;
				}

			}
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_label');
			

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				$this->Item->add_variation_data($suggestions, $search, $price_field);
				return $suggestions;
			}

      // Query 2
			// $this->db->select("$quantity_field as quantity,item_variations.item_number as item_variation_number,item_variations.deleted as variation_deleted,items.*, item_variations.id as item_variation_id, item_images.image_id,categories.name as category", false);
			if ($price_field) {
			  $this->db->select('items.'. $price_field); 
			}
			$this->db->select("$quantity_field as quantity, item_variations.item_number as item_variation_number, item_variations.deleted as variation_deleted, items.item_id, items.name, items.item_number, items.product_id, item_variations.id as item_variation_id, items.main_image_id as image_id,categories.name as category", false);
      // $this->db->select("$quantity_field as quantity, item_variations.deleted as variation_deleted, item_variations.id as item_variation_id, item_images.image_id, categories.name as category", false);

			$this->db->from('items');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
			}
			
			$this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_item_variations', "`$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location",'left');
			}
			
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->group_by('items.item_id, item_variations.id');
			$this->db->group_start(); 
			$this->db->like($this->db->dbprefix('items').'.item_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->or_like($this->db->dbprefix('item_variations').'.item_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->group_end(); 
			$this->db->limit($limit);

			$by_item_number = $this->db->get();

			$temp_suggestions = array();

			foreach($by_item_number->result() as $row)
			{
				$data = array(
					'label' => $row->item_variation_number ? $row->item_variation_number : $row->item_number.' ('.$row->name.') - '.($price_field ? to_currency($row->$price_field) : ''),
					'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'category' => $row->category,
					'quantity' => $row->quantity,
					'item_number' => $row->item_number,
					'variation_id' => $row->variation_deleted ? NULL : $row->item_variation_id,
				);
				
				$key = $row->item_variation_id ? $row->item_id . ($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
				
				$temp_suggestions[$key] = $data;
			}

			uasort($temp_suggestions, 'sort_assoc_array_by_label');

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				$this->Item->add_variation_data($suggestions, $search, $price_field);
				return $suggestions;
			}

      // Query 3
      if ($price_field) {
			  $this->db->select('items.'. $price_field);
			}
			$this->db->select("$quantity_field as quantity,item_variations.deleted as variation_deleted,items.item_id, items.name, items.item_number, items.product_id, item_variations.id as item_variation_id, items.main_image_id as image_id, categories.name as category", false);
			$this->db->from('items');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
			}
			
			$this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_item_variations', "`$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location",'left');
			}
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where('items.deleted',$deleted);
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->where('items.system_item',0);
			$this->db->like($this->db->dbprefix('items').'.product_id', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			$this->db->group_by('items.item_id, item_variations.id');
			$this->db->limit($limit);

			$by_product_id = $this->db->get();

			$temp_suggestions = array();

			foreach($by_product_id->result() as $row)
			{
				$data = array(
					'label' => $row->product_id.' ('.$row->name.') - '.($price_field ? to_currency($row->$price_field) : ''),
					'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'category' => $row->category,
					'quantity' => $row->quantity,
					'item_number' => $row->item_number,
					'variation_id' => $row->variation_deleted ? NULL : $row->item_variation_id,
				);
				
				$key = $row->item_variation_id ? $row->item_id .($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
				
				$temp_suggestions[$key] = $data;
			}

			uasort($temp_suggestions, 'sort_assoc_array_by_label');

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				$this->Item->add_variation_data($suggestions, $search, $price_field);
				return $suggestions;
			}

      // Query 4

			$this->db->select("$quantity_field as quantity,additional_item_numbers.*, item_variations.id as item_variation_id, items.main_image_id as image_id, items.unit_price, items.cost_price, categories.name as category", false);
			$this->db->from('additional_item_numbers');
			$this->db->join('items', 'additional_item_numbers.item_id = items.item_id');
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id='.$current_location, 'left');
			}
			
			$this->db->join('item_variations', 'items.item_id = item_variations.item_id','left');
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->join('location_item_variations', "`$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location",'left');
			}
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->group_by('items.item_id, item_variations.id');
			$this->db->like($this->db->dbprefix('additional_item_numbers').'.item_number', $search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
			if ($hide_inactive)
			{
				$this->db->where('item_inactive',0);
			}
			
			$this->db->limit($limit);

			$by_additional_item_numbers = $this->db->get();
			$temp_suggestions = array();
			foreach($by_additional_item_numbers->result() as $row)
			{
				$data = array(
					'label' => $row->item_number. ' - '.($price_field ? to_currency($row->$price_field) : ''),
					'image' => $row->image_id ?  app_file_url($row->image_id) : base_url()."assets/img/item.png" ,
					'category' => $row->category,
					'quantity' => $row->quantity,
					'item_number' => $row->item_number,
					'variation_id' => $row->item_variation_id,
				);
				
				$key = $row->item_variation_id ? $row->item_id .($row->variation_deleted ? '' : '#'.$row->item_variation_id ) : $row->item_id;
				
				$temp_suggestions[$key] = $data;
			}

			uasort($temp_suggestions, 'sort_assoc_array_by_label');

			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['label'], 'image' => $value['image'], 'category' => $value['category'],'quantity' => to_quantity($value['quantity']), 'item_number' => $value['item_number'], 'variation_id' => $value['variation_id']);
			}

			$suggestions = $this->filter_location_item($suggestions);
			if ( $this->Item->is_array_full($suggestions, $limit) )
			{
				$this->Item->add_variation_data($suggestions, $search, $price_field);
				return $suggestions;
			}
		}

    	$this->Item->add_variation_data($suggestions, $search, $price_field);

		$suggestions = $this->filter_location_item($suggestions);
		return $suggestions;
	}
	
	/*
		Preform a search on items
		$location_id = NULL: current employee location, 0: all location
	*/

	function search($search, $deleted=0,$category_id = false, $limit=20, $offset=0, $column='name', $orderby='asc', $fields = 'all')
	{

		if ($fields == $this->db->dbprefix('tags').'.name')
		{
			$tag_id = $this->Tag->get_tag_id_by_name($search);
		
			if (!$tag_id)
			{
				$tag_id = -1;
			}
		}
		
		if ($fields == $this->db->dbprefix('items').'.ecommerce_product_id')
		{
			$search = (string)$search;
		}

		$see_all_items = $this->Employee->has_module_action_permission('items','see_all_items',$this->Employee->get_logged_in_employee_info()->person_id);

		$phppos_categories = $this->db->dbprefix('categories');
		$phppos_tags = $this->db->dbprefix('tags');
		$phppos_items_tags = $this->db->dbprefix('items_tags');
		$phppos_suppliers = $this->db->dbprefix('suppliers');
		$phppos_location_items = $this->db->dbprefix('location_items');
		$phppos_location_item_variations = $this->db->dbprefix('location_item_variations');
		$phppos_tax_classes = $this->db->dbprefix('tax_classes');
		$phppos_item_images = $this->db->dbprefix('item_images');
		$phppos_item_variations = $this->db->dbprefix('item_variations');
		$phppos_manufacturers = $this->db->dbprefix('manufacturers');
		$phppos_additional_item_numbers = $this->db->dbprefix('additional_item_numbers');
		$phppos_items = $this->db->dbprefix('items');
		$phppos_location_ban_items = $this->db->dbprefix('location_ban_items');

		$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		$are_any_items_banned = $this->are_any_items_banned($location_id);
		if($see_all_items){
			$location_ban_item_list_query_left_join = "";
			$location_ban_item_list_query_where = "";
		}else{
			$location_ban_item_list_query_left_join = " LEFT JOIN `$phppos_location_ban_items` ON (`$phppos_items`.`item_id` = $phppos_location_ban_items.`item_id` and `$phppos_location_ban_items`.`location_id`=$location_id ) ";
			$location_ban_item_list_query_where = " and `$phppos_location_ban_items`.`location_id` is NULL ";
		}

		if (!$this->config->item('speed_up_search_queries'))
		{	
			$quantity_field ="IF(SUM(if($phppos_item_variations.deleted=0,1, 0))  > 0, 
				SUM(IF($phppos_item_variations.deleted=0, $phppos_location_item_variations.quantity, 0)) ".
				(($search && $fields == 'all') ?  "/ GREATEST(count(DISTINCT $phppos_additional_item_numbers.item_number), 1) " : "") .
				",$phppos_location_items.quantity)";
		}
		else
		{
			$quantity_field = "$phppos_location_items.quantity";
		}


		$custom_fields = array();
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{					
			if ($this->get_custom_field($k) !== FALSE)
			{
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$custom_fields[$k]=$this->db->dbprefix('items').".custom_field_${k}_value LIKE '".$this->db->escape_like_str($search)."%' ESCAPE '!'";
				}
				else
				{							
					$custom_fields[$k]= "(".$this->db->dbprefix('items').".custom_field_${k}_value IS NOT NULL and ".$this->db->dbprefix('items').".custom_field_${k}_value != 0 and FROM_UNIXTIME(".$this->db->dbprefix('items').".custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search))).')';					
				}

			}	
		}

		if (!empty($custom_fields))
		{				
			$custom_fields = implode(' or ',$custom_fields);
		}
		else
		{
			$custom_fields='1=2';
		}
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->distinct();
		}
		
		if ($category_id)
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{	
				$this->load->model('Category');
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($category_id);			
			}
			else
			{
				$category_ids = array($category_id);
			}
		}
		
		$this->load->model('Item_attribute');
		$attribute_count = $this->Item_attribute->count_all();
		
		if ($attribute_count > 0 && !$this->config->item('speed_up_search_queries'))
		{
			$column = $this->db->escape_str($column);
			$orderby = $this->db->escape_str($orderby);
			$limit = $this->db->escape_str($limit);
			
			$order_by = '';
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$order_by = "ORDER BY $column $orderby";
			}
			
			$offset = $this->db->escape_str($offset);
			$deleted = $this->db->escape_str($deleted);
			
			$manufacturers_join = '';
			$additional_item_numbers_join = '';
			
			if ($fields == $this->db->dbprefix('manufacturers').'.name')
			{
				$manufacturers_join = "LEFT JOIN `$phppos_manufacturers` 
					ON `$phppos_manufacturers`.`id` = $phppos_items.manufacturer_id";
			}
			
			$search_main_query = '';
			$search_overall_query = '';
			$having_main_query = '';
			
			if ($fields == 'all')
			{
				if ($search)
				{
					if($this->config->item('supports_full_text') && $this->config->item('enhanced_search_method'))
					{							
						if ($this->config->item('speed_up_search_queries'))
						{	
							$search_main_query = "WHERE ($custom_fields or MATCH (".$this->db->dbprefix('items').".name, ".$this->db->dbprefix('items').".item_number, product_id, description) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") or ".$this->db->dbprefix('items').".item_id = ".$this->db->escape($search).") and ".$this->db->dbprefix('items'). ".deleted=$deleted and system_item = 0";
						}
						else
						{						
							$additional_item_numbers_join = "LEFT JOIN `$phppos_additional_item_numbers` 
								ON `$phppos_additional_item_numbers`.`item_id` = $phppos_items.item_id";
							$search_main_query = "WHERE ($custom_fields or MATCH (".$this->db->dbprefix('items').".name, ".$this->db->dbprefix('items').".item_number, product_id, description) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") or ".$this->db->dbprefix('categories').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".$this->db->dbprefix('additional_item_numbers').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".$this->db->dbprefix('items').".item_id = ".$this->db->escape($search)." or ".$this->db->dbprefix('item_variations').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!') and ".$this->db->dbprefix('items'). ".deleted=$deleted and system_item = 0";		
						}
					}
					else
					{
						
						if ($this->config->item('speed_up_search_queries'))
						{
							$sql_search_name_criteria = $this->db->dbprefix('items').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_str($search)."%' ESCAPE '!'";
						}
						else
						{
							$search_terms_array=explode(" ", $this->db->escape_like_str($search));
	
							//to keep track of which search term of the array we're looking at now	
							$search_name_criteria_counter=0;
							$sql_search_name_criteria = '';
							//loop through array of search terms
							foreach ($search_terms_array as $x)
							{
								$sql_search_name_criteria.=
								($search_name_criteria_counter > 0 ? " AND " : "").
								$this->db->dbprefix('items').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$x."%' ESCAPE '!'";
								$search_name_criteria_counter++;
							}
						}
					
							if ($this->config->item('speed_up_search_queries'))
						{
							$search_main_query ="WHERE ((".
							$sql_search_name_criteria. ") or 
							".$this->db->dbprefix('items').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							"product_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							$this->db->dbprefix('items').".item_id = ".$this->db->escape($search)." or ".
							$this->db->dbprefix('categories').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or $custom_fields) and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0";
						}
						else
						{
							$additional_item_numbers_join = "LEFT JOIN `$phppos_additional_item_numbers` 
								ON `$phppos_additional_item_numbers`.`item_id` = $phppos_items.item_id";
						
							$search_main_query = "WHERE ((".
							$sql_search_name_criteria. ") or ". 
							$this->db->dbprefix('items').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							"product_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							$this->db->dbprefix('items').".item_id =".$this->db->escape($search)." or ".
							$this->db->dbprefix('additional_item_numbers').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							$this->db->dbprefix('item_variations').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							$this->db->dbprefix('categories').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or $custom_fields
							
							) and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0";
						}
					}			
				}
			}
			else
			{			
				if ($search)
				{
					//Exact Match fields
					if ($fields == $this->db->dbprefix('items').'.ecommerce_product_id' || $fields == $this->db->dbprefix('items').'.item_id' || $fields == $this->db->dbprefix('items').'.reorder_level' 
						|| $fields == $this->db->dbprefix('location_items').'.quantity'
						|| $fields == $this->db->dbprefix('items').'.cost_price' || $fields == $this->db->dbprefix('items').'.unit_price' || $fields == $this->db->dbprefix('items').'.promo_price' || $fields == $this->db->dbprefix('tags').'.name' || $fields == $this->db->dbprefix('suppliers').'.company_name' || $fields == $this->db->dbprefix('manufacturers').'.name')
					{
					
						if ($fields == $this->db->dbprefix('location_items').'.quantity')
						{
							$having_main_query = "HAVING quantity = ".$this->db->escape($search);
							$search_main_query = "WHERE $phppos_items.deleted=$deleted and system_item = 0";
						}
						elseif($fields == $this->db->dbprefix('tags').'.name' )
						{
							$search_main_query = "WHERE ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0";							
							$search_overall_query = "WHERE tags LIKE \"%".$this->db->escape_like_str($search).'%" or tags LIKE "%'.$tag_id.'%"';			
						}
						else
						{
							$search_main_query = "WHERE $fields = ".$this->db->escape($search)." and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0";								
						}
					}
					else
					{
						if($this->config->item('supports_full_text') && $this->config->item('enhanced_search_method'))
						{
							//Fulltext
							$search_main_query = "WHERE MATCH($fields) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0";												
						}
						else
						{
							$search_main_query = "WHERE $fields LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'";
							$search_main_query.=" and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0";
						}
					}
				}
			}
			
			if(isset($category_ids) && !empty($category_ids))
			{
				$search_main_query.=" and ".$phppos_categories.'.id IN('.implode(',',$category_ids).')';
			}
				
			if (!$search) //If we don't have a search make sure we filter out $deleted items
			{
				$search_main_query='WHERE '.$this->db->dbprefix('items').".deleted=$deleted and system_item = 0";
				
				if(isset($category_ids) && !empty($category_ids))
				{
					$search_main_query .= " and ".$phppos_categories.'.id IN('.implode(',',$category_ids).')';
				}
			}
			$query = "SELECT SQL_CALC_FOUND_ROWS main_query.*
			FROM (
				SELECT ".$this->db->escape(lang('common_inv'))." as `inventory`, `$phppos_suppliers`.`company_name` as `supplier_company_name`, `$phppos_location_items`.`location` as `location`, `$phppos_items`.*,
				`$phppos_categories`.`name` as `category`,
				 SUM(if($phppos_item_variations.deleted=0,1, 0)) as variation_count,  IF(SUM(if($phppos_item_variations.deleted=0,1, 0))  > 0,1,0) as has_variations,
				`$phppos_location_items`.`reorder_level` as `location_reorder_level`,
				`$phppos_location_items`.`cost_price` as `location_cost_price`, `$phppos_location_items`.`unit_price` as `location_unit_price`, `$phppos_tax_classes`.`name` as `tax_group`,
			 	$quantity_field as quantity,
				`$phppos_items`.`main_image_id` as image_id
				FROM `$phppos_items`
					$manufacturers_join
					$additional_item_numbers_join
					LEFT JOIN `$phppos_location_items`
						ON `$phppos_location_items`.`item_id` = `$phppos_items`.`item_id` and `$phppos_location_items`.`location_id` = $current_location
	        LEFT JOIN `$phppos_item_variations`
	           ON `$phppos_item_variations`.`item_id` = `$phppos_items`.`item_id`
	        LEFT JOIN `$phppos_location_item_variations`
	          ON `$phppos_location_item_variations`.`item_variation_id` = `$phppos_item_variations`.`id` and `$phppos_location_item_variations`.`location_id` = $current_location
					LEFT JOIN `$phppos_tax_classes`
						ON `$phppos_tax_classes`.`id` = $phppos_items.tax_class_id
					LEFT JOIN `$phppos_suppliers`
						ON $phppos_items.supplier_id = `$phppos_suppliers`.`person_id`
					LEFT JOIN `$phppos_categories`
						ON `$phppos_categories`.`id` = $phppos_items.category_id
					$location_ban_item_list_query_left_join
					$search_main_query $location_ban_item_list_query_where
	        GROUP BY `$phppos_items`.`item_id`
					$having_main_query
					 ) as main_query
          $search_overall_query
          $order_by
          LIMIT $limit OFFSET $offset";

					return $this->db->query($query);
		}
		else
		{
			
			if (!$this->config->item('speed_up_search_queries'))
			{
			  $this->db->protect_identifiers('SQL_CALC_FOUND_ROWS');
			  $this->db->select('SQL_CALC_FOUND_ROWS 1 as _hacked1', false);
				$this->db->select('"'.lang('common_inv').'" as inventory, suppliers.company_name as supplier_company_name,location_items.location as location, items.main_image_id as image_id, items.*, categories.id as category_id, categories.name as category,
				location_items.quantity as quantity,0 as variation_count, 0 as has_variations, 
				location_items.reorder_level as location_reorder_level,
				location_items.cost_price as location_cost_price,
				location_items.unit_price as location_unit_price,
				tax_classes.name as tax_group
				');
			}
			else
			{
			  $this->db->protect_identifiers('SQL_CALC_FOUND_ROWS');
			  $this->db->select('SQL_CALC_FOUND_ROWS 1 as _hacked2', false);
				$this->db->select('"'.lang('common_inv').'" as inventory, suppliers.company_name as supplier_company_name,location_items.location as location, items.main_image_id as image_id, items.*, categories.id as category_id, categories.name as category,
				location_items.quantity as quantity,0 as variation_count, 0 as has_variations, 
				location_items.reorder_level as location_reorder_level,
				location_items.cost_price as location_cost_price,
				location_items.unit_price as location_unit_price,
				tax_classes.name as tax_group
				');
			}
			$this->db->from('items');
			$this->db->join('tax_classes', 'tax_classes.id = items.tax_class_id', 'left');
			if(!$see_all_items)
			$this->db->join('location_ban_items', 'location_ban_items.item_id = items.item_id and location_ban_items.location_id = '.$location_id , 'left');
			$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
			
			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->group_by('items.item_id');
			}
			if ($fields == $this->db->dbprefix('manufacturers').'.name')
			{
				$this->db->join('manufacturers', 'items.manufacturer_id = manufacturers.id', 'left');
			}
						
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id = '.$current_location, 'left');

			if ($fields == 'all')
			{
				if ($search)
				{
					if($this->config->item('supports_full_text') && $this->config->item('enhanced_search_method'))
					{
						if ($this->config->item('speed_up_search_queries'))
						{	
							$this->db->where("($custom_fields or MATCH (".$this->db->dbprefix('items').".name, ".$this->db->dbprefix('items').".item_number, product_id, description) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") or ".$this->db->dbprefix('items').".item_id = ".$this->db->escape($search).") and ".$this->db->dbprefix('items'). ".deleted=$deleted and system_item = 0", NULL, FALSE);							
						}
						else
						{						
							$this->db->join('additional_item_numbers', 'additional_item_numbers.item_id = items.item_id', 'left');
							$this->db->where("($custom_fields or MATCH (".$this->db->dbprefix('items').".name, ".$this->db->dbprefix('items').".item_number, product_id, description) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") or ".$this->db->dbprefix('items').".tags LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'"." or ".$this->db->dbprefix('categories').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".$this->db->dbprefix('additional_item_numbers').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".$this->db->dbprefix('items').".item_id = ".$this->db->escape($search).") and ".$this->db->dbprefix('items'). ".deleted=$deleted and system_item = 0", NULL, FALSE);		
						}
					}
					else
					{
						if ($this->config->item('speed_up_search_queries'))
						{
							$sql_search_name_criteria = $this->db->dbprefix('items').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_str($search)."%' ESCAPE '!'";
						}
						else
						{
							$search_terms_array=explode(" ", $this->db->escape_like_str($search));
	
							//to keep track of which search term of the array we're looking at now	
							$search_name_criteria_counter=0;
							$sql_search_name_criteria = '';
							//loop through array of search terms
							foreach ($search_terms_array as $x)
							{
								$sql_search_name_criteria.=
								($search_name_criteria_counter > 0 ? " AND " : "").
								$this->db->dbprefix('items').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$x."%' ESCAPE '!'";
								$search_name_criteria_counter++;
							}
						}
					
						if ($this->config->item('speed_up_search_queries'))
						{
							$this->db->where("((".
							$sql_search_name_criteria. ") or 
							item_number LIKE '".$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							"product_id LIKE '".$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							$this->db->dbprefix('items').".item_id = ".$this->db->escape($search)." or ".
							$this->db->dbprefix('categories').".name LIKE '".$this->db->escape_like_str($search)."%' ESCAPE '!' or $custom_fields) and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0");
						}
						else
						{
							$this->db->join('additional_item_numbers', 'additional_item_numbers.item_id = items.item_id', 'left');
						
							$this->db->where("((".
							$sql_search_name_criteria. ") or ". 
							$this->db->dbprefix('items').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							"product_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							$this->db->dbprefix('items').".item_id =".$this->db->escape($search)." or ".
							$this->db->dbprefix('items').".tags LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							$this->db->dbprefix('additional_item_numbers').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
							$this->db->dbprefix('categories').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or $custom_fields
							
							) and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0");
						}
					}			
				}
			}
			else
			{			
				if ($search && $fields != $this->db->dbprefix('tags').'.name')
				{
					//Exact Match fields
					if ($fields == $this->db->dbprefix('items').'.ecommerce_product_id' || $fields == $this->db->dbprefix('items').'.item_id' || $fields == $this->db->dbprefix('items').'.reorder_level' 
						|| $fields == $this->db->dbprefix('location_items').'.quantity'
						|| $fields == $this->db->dbprefix('items').'.cost_price' || $fields == $this->db->dbprefix('items').'.unit_price' || $fields == $this->db->dbprefix('items').'.promo_price' || $fields == $this->db->dbprefix('suppliers').'.company_name' || $fields == $this->db->dbprefix('manufacturers').'.name')
					{
					
						$this->db->where("$fields = ".$this->db->escape($search)." and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0");								
					}
					else
					{
						if($this->config->item('supports_full_text') && $this->config->item('enhanced_search_method'))
						{
							//Fulltext
							$this->db->where("MATCH($fields) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0");												
						}
						else
						{
							$this->db->like($fields, $search, $search, !$this->config->item('speed_up_search_queries') ? 'both' : 'after');
							$this->db->where($this->db->dbprefix('items').".deleted=$deleted and system_item = 0");																		
						}
					}
				}
				else
				{
					
					$this->db->group_start();
					$this->db->like($this->db->dbprefix('items').'.tags', $search, 'both');
					$this->db->or_like($this->db->dbprefix('items').'.tags', $tag_id, 'both');
					$this->db->group_end();
				}
			}

			if(!$see_all_items){
				if($are_any_items_banned)
				{
					$this->db->where($this->db->dbprefix('location_ban_items').'.item_id', NULL);
				}
			}

			if(isset($category_ids) && !empty($category_ids))
			{
				$this->db->where_in('categories.id', $category_ids);
			}

			if (!$this->config->item('speed_up_search_queries'))
			{
				$this->db->order_by($column, $orderby);
			}

			if (!$search) //If we don't have a search make sure we filter out deleted items
			{
				$this->db->where('items.deleted', $deleted);
				$this->db->where('items.system_item',0);
			}

			$this->db->limit($limit);
			$this->db->offset($offset);
			return $this->db->get();
		}
	}
	
	
	//This is more of an estimation (for performance reasons) as we aren't doing a search for all fields. We might change in future
	function search_count_all($search, $deleted = 0,$category_id = FALSE, $limit=10000, $fields = 'all')
	{
		
		if ($fields == $this->db->dbprefix('items').'.ecommerce_product_id')
		{
			$search = (string)$search;
		}
		
		$see_all_items = $this->Employee->has_module_action_permission('items','see_all_items',$this->Employee->get_logged_in_employee_info()->person_id);
		$custom_fields = array();
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{					
			if ($this->get_custom_field($k) !== FALSE)
			{
				if ($this->get_custom_field($k,'type') != 'date')
				{
					$custom_fields[$k]=$this->db->dbprefix('items').".custom_field_${k}_value LIKE '".$this->db->escape_like_str($search)."%' ESCAPE '!'";
				}
				else
				{							
					$custom_fields[$k]= "(".$this->db->dbprefix('items').".custom_field_${k}_value IS NOT NULL and ".$this->db->dbprefix('items').".custom_field_${k}_value != 0 and FROM_UNIXTIME(".$this->db->dbprefix('items').".custom_field_${k}_value, '%Y-%m-%d') = ".$this->db->escape(date('Y-m-d', strtotime($search))).')';					
				}

			}	
		}

		if (!empty($custom_fields))
		{				
			$custom_fields = implode(' or ',$custom_fields);
		}
		else
		{
			$custom_fields='1=2';
		}
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		$current_location=$this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->distinct();
		}
		else
		{
			return $limit;
		}
		
		if ($category_id)
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{	
				$this->load->model('Category');
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($category_id);			
			}
			else
			{
				$category_ids = array($category_id);
			}
		}
		
		
		$this->db->select('items.*,categories.id as category_id,categories.name as category,
		location_items.quantity as quantity, 
		location_items.reorder_level as location_reorder_level,
		location_items.cost_price as location_cost_price,
		location_items.unit_price as location_unit_price');
		$this->db->from('items');
		
		$this->db->join('items_tags', 'items_tags.item_id = items.item_id', 'left');
		$this->db->join('tags', 'tags.id = items_tags.tag_id', 'left');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');

		if(!$see_all_items){
			$this->db->join('location_ban_items', 'items.item_id = location_ban_items.item_id and location_ban_items.location_id = '.$current_location, 'left');
			$this->db->where('location_ban_items.location_id IS NULL');
		}

		$this->db->group_by('items.item_id');
		
		if ($fields == $this->db->dbprefix('manufacturers').'.name')
		{
			$this->db->join('manufacturers', 'items.manufacturer_id = manufacturers.id', 'left');
		}
		
		$this->db->join('categories', 'categories.id = items.category_id','left');
		$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id = '.$current_location, 'left');
		
		if ($fields == 'all')
		{
			if ($search)
			{
				if($this->config->item('supports_full_text') && $this->config->item('enhanced_search_method'))
				{
					if ($this->config->item('speed_up_search_queries'))
					{
						$this->db->where("($custom_fields or MATCH (".$this->db->dbprefix('items').".name, ".$this->db->dbprefix('items').".item_number, product_id, description) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") and ".$this->db->dbprefix('items'). ".deleted=$deleted and system_item = 0", NULL, FALSE);							
					}
					else
					{
						$this->db->join('additional_item_numbers', 'additional_item_numbers.item_id = items.item_id', 'left');
						$this->db->where("($custom_fields or MATCH (".$this->db->dbprefix('items').".name, ".$this->db->dbprefix('items').".item_number, product_id, description) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") or ".$this->db->dbprefix('items').".tags LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!'"." or ".$this->db->dbprefix('categories').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".$this->db->dbprefix('additional_item_numbers').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".$this->db->dbprefix('items').".item_id = ".$this->db->escape($search).") and ".$this->db->dbprefix('items'). ".deleted=$deleted and system_item = 0", NULL, FALSE);		
					}
				}
				else
				{
					if ($this->config->item('speed_up_search_queries'))
					{
						$sql_search_name_criteria = $this->db->dbprefix('items').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_str($search)."%' ESCAPE '!'";
					}
					else
					{
						$search_terms_array=explode(" ", $this->db->escape_like_str($search));

						//to keep track of which search term of the array we're looking at now	
						$search_name_criteria_counter=0;
						$sql_search_name_criteria = '';
						//loop through array of search terms
						foreach ($search_terms_array as $x)
						{
							$sql_search_name_criteria.=
							($search_name_criteria_counter > 0 ? " AND " : "").
							$this->db->dbprefix('items').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$x."%' ESCAPE '!'";
							$search_name_criteria_counter++;
						}
					}
					
					if ($this->config->item('speed_up_search_queries'))
					{
						$this->db->where("((".
						$sql_search_name_criteria. ") or 
						item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
						"product_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
						$this->db->dbprefix('items').".item_id = ".$this->db->escape($search)." or ".
						$this->db->dbprefix('categories').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or $custom_fields) and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0");
					}
					else
					{					
						$this->db->join('additional_item_numbers', 'additional_item_numbers.item_id = items.item_id', 'left');
						
						$this->db->where("((".
						$sql_search_name_criteria. ") or ". 
						$this->db->dbprefix('items').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
						"product_id LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
						$this->db->dbprefix('items').".item_id = ".$this->db->escape($search)." or ".
						$this->db->dbprefix('items').".tags LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
						$this->db->dbprefix('additional_item_numbers').".item_number LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or ".
						$this->db->dbprefix('categories').".name LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$this->db->escape_like_str($search)."%' ESCAPE '!' or $custom_fields
							
						) and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0");
					}
				}			
			}
		}
		else
		{			
			if ($search)
			{
				//Exact Match fields
				if ($fields == $this->db->dbprefix('items').'.item_id' || $fields == $this->db->dbprefix('items').'.reorder_level' 
					|| $fields == $this->db->dbprefix('location_items').'.quantity'
					|| $fields == $this->db->dbprefix('items').'.cost_price' || $fields == $this->db->dbprefix('items').'.unit_price' || $fields == $this->db->dbprefix('items').'.promo_price' || $fields == $this->db->dbprefix('tags').'.name' || $fields == $this->db->dbprefix('suppliers').'.company_name' || $fields == $this->db->dbprefix('manufacturers').'.name')
				{
					$this->db->where("$fields = ".$this->db->escape($search)." and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0");								
				}
				else
				{
					if($this->config->item('supports_full_text') && $this->config->item('enhanced_search_method'))
					{
						//Fulltext
						$this->db->where("MATCH($fields) AGAINST ('\"".$this->db->escape_str(escape_full_text_boolean_search($search))."\"' IN BOOLEAN MODE".") and ".$this->db->dbprefix('items').".deleted=$deleted and system_item = 0");												
					}
					else
					{
						$this->db->like($fields,$search,!$this->config->item('speed_up_search_queries') ? 'both' : 'after');
						$this->db->where($this->db->dbprefix('items').".deleted=$deleted and system_item = 0");																		
					}
				}
			}
		}
		
		if(isset($category_ids) && !empty($category_ids)) 
		{
			$this->db->where_in('categories.id', $category_ids);
		}
		
		if (!$search) //If we don't have a search make sure we filter out deleted items
		{
			$this->db->where('items.deleted', $deleted);
			$this->db->where('items.system_item',0);
		}
		
		$result=$this->db->get();		
		return $result->num_rows();
	}
	
	function cleanup()
	{
		$item_data = array('item_number' => null, 'product_id' => null);
		$this->db->where('deleted', 1);
		$return = $this->db->update('items',$item_data);
		
		if ($return)
		{
			
			$this->load->model('Additional_item_numbers');
			$this->Additional_item_numbers->cleanup();
			$this->load->model('Item_serial_number');
			$this->Item_serial_number->cleanup();
			$this->load->model('Item_variations');
			$this->Item_variations->cleanup();
			$item_tier_prices_table = $this->db->dbprefix('items_tier_prices');
			$items_table = $this->db->dbprefix('items');
			$item_images_table = $this->db->dbprefix('item_images');
			$app_files_table = $this->db->dbprefix('app_files');
			$this->db->query('SET FOREIGN_KEY_CHECKS = 0');
			$this->db->query("DELETE FROM $item_images_table WHERE item_id IN (SELECT item_id FROM $items_table WHERE deleted = 1)");
			$this->db->query('SET FOREIGN_KEY_CHECKS = 1');
			return $this->db->query("DELETE FROM $item_tier_prices_table WHERE item_id IN (SELECT item_id FROM $items_table WHERE deleted = 1)");
			
		}

		return false;
	}
	
	function add_image($item_id,$image_id)
	{
		$this->db->insert('item_images', array('item_id' => $item_id, 'image_id' => $image_id));
	}
	
	function set_main_image($item_id,$image_id)
	{
		$this->db->where('item_id', $item_id);
		$this->db->update('items', array('main_image_id' => $image_id));
	}
	
	
	function delete_image($image_id)
	{
		$this->db->where('main_image_id', $image_id);
		$this->db->update('items', array('main_image_id' => NULL));
		
	  $this->db->where('image_id',$image_id);
		$this->db->delete('item_images');
		$this->load->model('Appfile');
		return $this->Appfile->delete($image_id);
	}
	
	public function delete_all_images($item_id)
	{
		$this->db->where('item_id', $item_id);
		$this->db->update('items',array('main_image_id' => NULL));
		
		$this->db->from('item_images');
		$this->db->where('item_id',$item_id);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$this->delete_image($row['image_id']);
		}
	}
	
	function save_image_metadata($image_id, $title, $alt_text, $variation = NULL)
	{
		$this->db->where('image_id', $image_id);
		$this->db->update('item_images', array('title' => $title,'alt_text' => $alt_text, 'item_variation_id' => $variation));
	}
		
	function link_image_to_ecommerce($image_id,$ecommerce_image_id)
	{
		$this->db->where('image_id', $image_id);
		$this->db->update('item_images', array('ecommerce_image_id' => $ecommerce_image_id));	
	}
	
	function set_variation_for_ecommerce_image($ecommerce_image_id,$item_variation_id)
	{
		$this->db->where('ecommerce_image_id', $ecommerce_image_id);
		$this->db->update('item_images', array('item_variation_id' => $item_variation_id));	
	}
	
	
	function unlink_image_from_ecommerce($ecommerce_image_id)
	{
		$this->db->where('ecommerce_image_id', $ecommerce_image_id);
		$this->db->update('item_images', array('ecommerce_image_id' => NULL));
	}
	function create_or_update_fee_item($can_cache = TRUE)
	{
		$item_id = FALSE;
		
		$this->db->from('items');
		$this->db->where('product_id', lang('common_fee'));
		
		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$item_id = $query_result[0]->item_id;
		}
		
		$item_data = array(
			'name'			=>	lang('common_fee'),
			'product_id'	=>	lang('common_fee'),
			'description'	=>	'',
			'item_number'	=> NULL,
			'category_id'=> $this->Category->save(lang('common_fee'), TRUE, NULL, $this->Category->get_category_id(lang('common_fee'),$can_cache),FALSE,NULL,1),
			'size'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	0,
			'tax_included' => 0,
			'reorder_level'	=>	NULL,
			'allow_alt_description'=> 0,
			'is_serialized'=> 0,
			'is_service'=> 1,
			'override_default_tax' => 1,
			'deleted' => 0,
			'system_item' => 1,
			'disable_loyalty' => 1,
		);
		
		$this->save($item_data, $item_id);
		
		if ($item_id)
		{
			return $item_id;
		}
		else
		{
			return $item_data['item_id'];
		}
		
	}
	
	function create_or_update_integrated_gift_card_item($giftcard_value,$giftcard_number)
	{
		$item_id = FALSE;
		
		$this->db->from('items');
		$this->db->where('product_id', lang('common_integrated_gift_card'));
		
		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$item_id = $query_result[0]->item_id;
		}
		
		$item_data = array(
			'name'			=>	lang('common_integrated_gift_card'),
			'product_id'	=>	lang('common_integrated_gift_card'),
			'description'	=>	$giftcard_number,
			'item_number'	=> NULL,
			'category_id'=> $this->Category->save(lang('common_integrated_gift_card'), TRUE, NULL, $this->Category->get_category_id(lang('common_integrated_gift_card')),FALSE,NULL,1),
			'size'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	$giftcard_value,
			'tax_included' => 0,
			'reorder_level'	=>	NULL,
			'allow_alt_description'=> 0,
			'is_serialized'=> 0,
			'is_service'=> 1,
			'override_default_tax' => 1,
			'deleted' => 0,
			'system_item' => 1,
		);
		
		$this->save($item_data, $item_id);
		
		if ($item_id)
		{
			return $item_id;
		}
		else
		{
			return $item_data['item_id'];
		}
		
	}
	
	
	function create_or_update_refund_item($can_cache = TRUE)
	{
		$item_id = FALSE;
		
		$this->db->from('items');
		$this->db->where('product_id', lang('common_refund'));
		
		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$item_id = $query_result[0]->item_id;
		}
		
		$item_data = array(
			'name'			=>	lang('common_refund'),
			'product_id'	=>	lang('common_refund'),
			'description'	=>	'',
			'item_number'	=> NULL,
			'category_id'=> $this->Category->save(lang('common_refund'), TRUE, NULL, $this->Category->get_category_id(lang('common_refund'),$can_cache),FALSE,NULL,1),
			'size'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	0,
			'tax_included' => 0,
			'reorder_level'	=>	NULL,
			'allow_alt_description'=> 0,
			'is_serialized'=> 0,
			'is_service'=> 1,
			'override_default_tax' => 0,
			'deleted' => 0,
			'system_item' => 1,
		);
		
		$this->save($item_data, $item_id);
		
		if ($item_id)
		{
			return $item_id;
		}
		else
		{
			return $item_data['item_id'];
		}
		
	}
	
	
	function create_or_update_delivery_item($can_cache = TRUE)
	{
		$this->lang->load('sales');
		$item_id = FALSE;
		
		$this->db->from('items');
		$this->db->where('product_id', lang('common_delivery_fee'));
		
		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$item_id = $query_result[0]->item_id;
		}
		
		$item_data = array(
			'name'			=>	lang('common_delivery_fee'),
			'product_id'	=>	lang('common_delivery_fee'),
			'description'	=>	'',
			'item_number'	=> NULL,
			'category_id'=> $this->Category->save(lang('common_delivery_fee'), TRUE, NULL, $this->Category->get_category_id(lang('common_delivery_fee'),$can_cache),FALSE,NULL,1),
			'size'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	0,
			'tax_included' => 0,
			'reorder_level'	=>	NULL,
			'allow_alt_description'=> 0,
			'is_serialized'=> 0,
			'is_service'=> 1,
			'override_default_tax' => 0,
			'deleted' => 0,
			'system_item' => 1,
			'disable_loyalty' => 1,
		);
		
		$this->save($item_data, $item_id);
		
		if ($item_id)
		{
			return $item_id;
		}
		else
		{
			return $item_data['item_id'];
		}
		
	}
	
	function create_or_update_store_account_item()
	{
		$this->lang->load('sales');
		$item_id = FALSE;
		
		$this->db->from('items');
		$this->db->where('product_id', lang('common_store_account_payment'));

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$item_id = $query_result[0]->item_id;
		}
		
		$item_data = array(
			'name'			=>	lang('common_store_account_payment'),
			'product_id'	=>	lang('common_store_account_payment'),
			'description'	=>	'',
			'item_number'	=> NULL,			
			'category_id'=> $this->Category->save(lang('common_store_account_payment'), TRUE, NULL, $this->Category->get_category_id(lang('common_store_account_payment')),FALSE,NULL,1),
			'size'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	0,
			'tax_included' => 0,
			'reorder_level'	=>	NULL,
			'allow_alt_description'=> 0,
			'is_serialized'=> 0,
			'is_service'=> 1,
			'override_default_tax' => 1,
			'deleted' => 0,
			'system_item' => 1,
			'is_ecommerce' => 0,
		);
		
		$this->save($item_data, $item_id);
			
		if ($item_id)
		{
			return $item_id;
		}
		else
		{
			return $item_data['item_id'];
		}
	}
	
	function create_or_update_ecommerce_item()
	{
		$this->lang->load('sales');
		$item_id = FALSE;
		
		$this->db->from('items');
		$this->db->where('product_id', lang('common_ecommerce_item'));

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$item_id = $query_result[0]->item_id;
		}
		
		$item_data = array(
			'name'			=>	lang('common_ecommerce_item'),
			'product_id'	=>	lang('common_ecommerce_item'),
			'description'	=>	'',
			'item_number'	=> NULL,			
			'category_id'=> NULL,
			'size'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	0,
			'tax_included' => 0,
			'reorder_level'	=>	NULL,
			'allow_alt_description'=> 0,
			'is_serialized'=> 0,
			'is_service'=> 1,
			'override_default_tax' => 1,
			'deleted' => 0,
			'system_item' => 1,
			'is_ecommerce' => 0,
		);
		
		$this->save($item_data, $item_id);
			
		if ($item_id)
		{
			return $item_id;
		}
		else
		{
			return $item_data['item_id'];
		}
	}
	
	
	function create_or_update_purchase_points_item()
	{
		$this->lang->load('sales');
		$item_id = FALSE;
		
		$this->db->from('items');
		$this->db->where('product_id', lang('common_purchase_points'));

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$item_id = $query_result[0]->item_id;
		}
		
		$item_data = array(
			'name'			=>	lang('common_purchase_points'),
			'product_id'	=>	lang('common_purchase_points'),
			'description'	=>	'',
			'item_number'	=> NULL,			
			'category_id'=> $this->Category->save(lang('common_purchase_points'), TRUE, NULL, $this->Category->get_category_id(lang('common_purchase_points')),FALSE,NULL,1),
			'size'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	$this->config->item('point_value'),
			'tax_included' => 0,
			'reorder_level'	=>	NULL,
			'allow_alt_description'=> 0,
			'is_serialized'=> 0,
			'is_service'=> 1,
			'override_default_tax' => 1,
			'deleted' => 0,
			'system_item' => 0,
			'is_ecommerce' => 0,
			'disable_loyalty' => 1,
		);
		
		$this->save($item_data, $item_id);
			
		if ($item_id)
		{
			return $item_id;
		}
		else
		{
			return $item_data['item_id'];
		}
	}
	
	
	function create_or_update_flat_discount_item($tax_included = 0)
	{
		$item_id = FALSE;
		
		$this->db->from('items');
		$this->db->where('product_id', lang('common_discount'));

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$item_id = $query_result[0]->item_id;
		}
		
		$item_data = array(
			'name'			=>	lang('common_discount'),
			'product_id'	=>	lang('common_discount'),
			'description'	=>	'',
			'item_number'	=> NULL,
			'category_id'=> $this->Category->save(lang('common_discount'), TRUE, NULL, $this->Category->get_category_id(lang('common_discount')),FALSE,NULL,1),
			'size'			=> '',
			'cost_price'	=>	0,
			'unit_price'	=>	0,
			'tax_included' => $tax_included,
			'reorder_level'	=>	NULL,
			'allow_alt_description'=> 0,
			'is_serialized'=> 0,
			'is_service'=> 1,
			'override_default_tax' => 1,
			'deleted' => 0,
			'system_item' => 1,
			'is_ecommerce' => 0,
		);
		
		$this->save($item_data, $item_id);
			
		if ($item_id)
		{
			return $item_id;
		}
		else
		{
			return $item_data['item_id'];
		}
	}
	
	function get_item_id_for_delivery_item()
	{
		$this->db->from('items');
		$this->db->where('product_id', lang('common_delivery_fee'));
		$this->db->where('deleted', 0);

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			return $query_result[0]->item_id;
		}
		
		return FALSE;
	}
	
	function get_item_id_for_fee_item()
	{
		static $cache;
		
		if ($cache !== NULL)
		{
			return $cache;
		}
		$this->db->from('items');
		$this->db->where('product_id', lang('common_fee'));
		$this->db->where('deleted', 0);

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$cache = $query_result[0]->item_id;
			return $cache;
		}
		else
		{
			$cache = FALSE;
		}
		
		return FALSE;
	}
	
	
	function get_item_id_for_flat_discount_item()
	{
		static $cache;
		
		if ($cache !== NULL)
		{
			return $cache;
		}
		$this->db->from('items');
		$this->db->where('product_id', lang('common_discount'));
		$this->db->where('deleted', 0);

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$cache = $query_result[0]->item_id;
			return $cache;
		}
		else
		{
			$cache = FALSE;
		}
		
		return FALSE;
	}
	
	function get_store_account_item_id()
	{
		static $cache;
		
		if ($cache !== NULL)
		{
			return $cache;
		}
		
		$this->lang->load('sales');
		$this->db->from('items');
		$this->db->where('product_id', lang('common_store_account_payment'));
		$this->db->where('deleted', 0);

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$cache = $query_result[0]->item_id;
			return $cache;
		}
		else
		{
			$cache = FALSE;
		}
		
		return FALSE;
	}
	
	function get_non_taxable_item_ids()
	{
		$this->db->select('items.item_id');
		$this->db->from('items');
		$this->db->join('items_taxes', 'items_taxes.item_id = items.item_id', 'left');
		$this->db->where('override_default_tax', 1);
		$this->db->where('items_taxes.item_id IS NULL');
		
		$result = $this->db->get()->result_array();
		
		$return = array();
		
		foreach($result as $row)
		{
			$return[$row['item_id']] = TRUE;
		}
		
		return $return;
	}	
	
	function get_default_columns()
	{
		return array('item_id','item_number','name','category_id','cost_price','unit_price','quantity');

	}
	
	/*
	Gets sale price for item given an array of parameters. Current keys are item_id,quantity_unit_id,quantity_unit_quantity and tier_id
	*/
	function get_sale_price(array $params)
	{
		$quantity_unit_id = isset($params['quantity_unit_id']) ? $params['quantity_unit_id'] : NULL;
		
		$quantity_unit_quantity = isset($params['quantity_unit_quantity']) && $params['quantity_unit_quantity'] ? $params['quantity_unit_quantity'] : 1;
		$item_id = $params['item_id'];
		$tier_id = isset($params['tier_id']) ? $params['tier_id'] : FALSE;
		$variation_id = isset($params['variation_id']) ? $params['variation_id'] : FALSE;
		
		$this->load->model('Item_variations');
		$this->load->model('Item_variation_location');
		
		$item_info = $this->Item->get_info($item_id,false);
		$item_location_info = $this->Item_location->get_info($item_id);
		
		$item_tier_row = $this->Item->get_tier_price_row($tier_id, $item_id);
		$item_location_tier_row = $this->Item_location->get_tier_price_row($tier_id, $item_id, $this->Employee->get_logged_in_employee_current_location_id());
		
		$variation_info = $this->Item_variations->get_info($variation_id);
		$variation_location_info = $this->Item_variation_location->get_info($variation_id);
		
		
		$tier_info = $this->Tier->get_info($tier_id);
		
		
		if ($quantity_unit_id)
		{
			$qui = $this->Item->get_quantity_unit_info($quantity_unit_id);
					
			if ($qui->unit_price !== NULL)
			{
				return to_currency_no_money($item_unit_price = $qui->unit_price);
			}
			else
			{	
				return to_currency_no_money($this->get_sale_price(array('item_id' => $item_id,'tier_id' => $tier_id,'variation_id' => $variation_id,'quantity_unit_quantity' => $qui->unit_quantity)));
			}		
		}	
		elseif (!empty($item_location_tier_row) && $item_location_tier_row->unit_price)
		{
			return to_currency_no_money($item_location_tier_row->unit_price*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_location_tier_row) && $item_location_tier_row->percent_off)
		{
			if (($variation_info && $variation_info->unit_price )|| ($variation_location_info && $variation_location_info->unit_price))
			{
				$item_unit_price = (double)$variation_location_info->unit_price ?  $variation_location_info->unit_price : $variation_info->unit_price;
			}
			else
			{
				$item_unit_price = (double)$item_location_info->unit_price ? $item_location_info->unit_price : $item_info->unit_price;
			}
			return to_currency_no_money(($item_unit_price *(1-($item_location_tier_row->percent_off/100)))*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_location_tier_row) && $item_location_tier_row->cost_plus_percent)
		{
			if ($variation_info && $variation_info->cost_price)
			{
				$item_cost_price = $variation_info->cost_price;
			}
			else
			{
				$item_cost_price = (double)$item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
			}
			return to_currency_no_money(($item_cost_price *(1+($item_location_tier_row->cost_plus_percent/100)))*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_location_tier_row) && $item_location_tier_row->cost_plus_fixed_amount)
		{
			if ($variation_info && $variation_info->cost_price)
			{
				$item_cost_price = $variation_info->cost_price;
			}
			else
			{
				$item_cost_price = (double)$item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
			}
			return to_currency_no_money(($item_cost_price + $item_location_tier_row->cost_plus_fixed_amount)*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_tier_row) && $item_tier_row->unit_price)
		{
			return to_currency_no_money($item_tier_row->unit_price*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_tier_row) && $item_tier_row->percent_off)
		{
			if (($variation_info && $variation_info->unit_price )|| ($variation_location_info && $variation_location_info->unit_price))
			{
				$item_unit_price = (double)$variation_location_info->unit_price ?  $variation_location_info->unit_price : $variation_info->unit_price;
			}
			else
			{
				$item_unit_price = (double)$item_location_info->unit_price ? $item_location_info->unit_price : $item_info->unit_price;
			}
			return to_currency_no_money(($item_unit_price *(1-($item_tier_row->percent_off/100)))*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_tier_row) && $item_tier_row->cost_plus_percent)
		{
			if ($variation_info && $variation_info->cost_price)
			{
				$item_cost_price = $variation_info->cost_price;
			}
			else
			{
				$item_cost_price = (double)$item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
			}
			return to_currency_no_money(($item_cost_price *(1+($item_tier_row->cost_plus_percent/100)))*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif (!empty($item_tier_row) && $item_tier_row->cost_plus_fixed_amount)
		{
			if ($variation_info && $variation_info->cost_price)
			{
				$item_cost_price = $variation_info->cost_price;
			}
			else
			{
				$item_cost_price = (double)$item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
			}
			return to_currency_no_money(($item_cost_price + $item_tier_row->cost_plus_fixed_amount)*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif($tier_info->default_percent_off)
		{
			if (($variation_info && $variation_info->unit_price )|| ($variation_location_info && $variation_location_info->unit_price))
			{
				$item_unit_price = (double)$variation_location_info->unit_price ?  $variation_location_info->unit_price : $variation_info->unit_price;
			}
			else
			{
				$item_unit_price = (double)$item_location_info->unit_price ? $item_location_info->unit_price : $item_info->unit_price;
			}
			return to_currency_no_money(($item_unit_price *(1-($tier_info->default_percent_off/100)))*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif($tier_info->default_cost_plus_percent)
		{
			if (($variation_info && $variation_info->cost_price )|| ($variation_location_info && $variation_location_info->cost_price))
			{
				$item_cost_price = (double)$variation_location_info->cost_price ?  $variation_location_info->cost_price : $variation_info->cost_price;
			}
			else
			{
				$item_cost_price = (double)$item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
			}
			return to_currency_no_money(($item_cost_price *(1+($tier_info->default_cost_plus_percent/100)))*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif($tier_info->default_cost_plus_fixed_amount)
		{
			if (($variation_info && $variation_info->cost_price )|| ($variation_location_info && $variation_location_info->cost_price))
			{
				$item_cost_price = (double)$variation_location_info->cost_price ?  $variation_location_info->cost_price : $variation_info->cost_price;
			}
			else
			{
				$item_cost_price = (double)$item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
			}
			
			return to_currency_no_money(($item_cost_price + $tier_info->default_cost_plus_fixed_amount)*$quantity_unit_quantity, $this->config->item('round_tier_prices_to_2_decimals') ? 2 : 10);
		}
		elseif(($variation_id && $variation_info->unit_price) || ($variation_id && $variation_location_info && $variation_location_info->unit_price) || ($variation_id && $variation_info->promo_price) || ($variation_id && $variation_location_info && $variation_location_info->promo_price))
		{
			$today =  strtotime(date('Y-m-d'));
			
			$is_variation_date_promo = ($variation_info->start_date !== NULL && $variation_info->end_date !== NULL) && (strtotime($variation_info->start_date) <= $today && strtotime($variation_info->end_date) >= $today);
			
			if ((double)$variation_info->promo_price && $variation_info->start_date === NULL && $variation_info->end_date === NULL)
			{
				return to_currency_no_money($variation_info->promo_price*$quantity_unit_quantity, 10);
			}
			elseif ($is_variation_date_promo && (double)$variation_info->promo_price)
			{
				return to_currency_no_money($variation_info->promo_price*$quantity_unit_quantity, 10);
			}
		
			return to_currency_no_money(((double)$variation_location_info->unit_price ? $variation_location_info->unit_price : $variation_info->unit_price)*$quantity_unit_quantity, 10);
		}
		else
		{
			$today =  strtotime(date('Y-m-d'));
			$is_item_location_date_promo = ($item_location_info->start_date !== NULL && $item_location_info->end_date !== NULL) && (strtotime($item_location_info->start_date) <= $today && strtotime($item_location_info->end_date) >= $today);
			$is_item_date_promo = ($item_info->start_date !== NULL && $item_info->end_date !== NULL) && (strtotime($item_info->start_date) <= $today && strtotime($item_info->end_date) >= $today);
			
			if ((double)$item_location_info->promo_price && $item_location_info->start_date === NULL && $item_location_info->end_date === NULL)
			{
				return to_currency_no_money($item_location_info->promo_price*$quantity_unit_quantity, 10);
			}
			elseif ((double)$item_info->promo_price && $item_info->start_date === NULL && $item_info->end_date === NULL)
			{
				return to_currency_no_money($item_info->promo_price*$quantity_unit_quantity, 10);
			}
			elseif ($is_item_location_date_promo && (double)$item_location_info->promo_price)
			{
				return to_currency_no_money($item_location_info->promo_price*$quantity_unit_quantity, 10);
			}
			elseif ($is_item_date_promo && (double)$item_info->promo_price)
			{
				return to_currency_no_money($item_info->promo_price*$quantity_unit_quantity, 10);
			}
			else
			{
				$item_unit_price = (double)$item_location_info->unit_price ? (double)$item_location_info->unit_price : (double)$item_info->unit_price;
				
				return @to_currency_no_money($item_unit_price*$quantity_unit_quantity, 10);
			}
		}
	}
	
	function set_last_edited($item_id)
	{
		$now = date('Y-m-d H:i:s');
		$this->db->where('item_id',$item_id);
		return $this->db->update('items',array('last_edited' => $now));
	}
	
	function get_quantity_unit_info($id)
	{
		$this->db->from('items_quantity_units');
		$this->db->where('id',$id);
		$this->db->order_by('id');
		
		return $this->db->get()->row();
	}
	
	function get_quantity_units($item_id)
	{
		$this->db->from('items_quantity_units');
		$this->db->where('item_id',$item_id);
		$this->db->order_by('id');
		
		return $this->db->get()->result();
	}
	
	function unit_quantity_exists($id)
	{
		$this->db->from('items_quantity_units');
		$this->db->where('id',$id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	function quantity_unit_item_number_exists($number)
	{
		$this->db->from('items_quantity_units');
		$this->db->where('quantity_unit_item_number',$number);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	
	function save_unit_quantity(&$unit_quantity_data, $id = false)
	{
		if (!$id or !$this->unit_quantity_exists($id))
		{
			if($this->db->insert('items_quantity_units',$unit_quantity_data))
			{
				$unit_quantity_data['id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('id', $id);
		return $this->db->update('items_quantity_units',$unit_quantity_data);
	}
	
	function delete_quantity_unit($quantity_unit_id)
	{
		
		$this->db->where('items_quantity_units_id',$quantity_unit_id);
		$this->db->update('sales_items',array('items_quantity_units_id' => NULL));
		
		$this->db->where('items_quantity_units_id',$quantity_unit_id);
		$this->db->update('receivings_items',array('items_quantity_units_id' => NULL));
		
		
		$this->db->where('id', $quantity_unit_id);
		$this->db->delete('items_quantity_units');
	}
	
	function save_price_history($item_id,$item_variation_id,$location_id,$unit_price,$cost_price, $force=FALSE)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info() && $this->Employee->get_logged_in_employee_info()->person_id ? $this->Employee->get_logged_in_employee_info()->person_id : 1;
		
		if ($location_id)
		{
			if ($item_variation_id)
			{
				$item_info = $this->Item_variation_location->get_info($item_variation_id,$location_id);
			}
			else
			{
				$item_info = $this->Item_location->get_info($item_id,$location_id);			
		
			}
		}
		else
		{
			if ($item_variation_id)
			{
				$item_info = $this->Item_variations->get_info($item_variation_id);
			}
			else
			{
				$item_info = $this->get_info($item_id);
			}
		}
		
		if ($item_info->unit_price != $unit_price || $item_info->cost_price!=$cost_price || $force)
		{
			$this->db->insert('items_pricing_history', array(
			'on_date' => date('Y-m-d H:i:s'),
			'employee_id' => $employee_id,
			'item_id' => $item_id,
			'item_variation_id' => $item_variation_id,
			'location_id' => $location_id,
			'unit_price' => $unit_price,
			'cost_price' => $cost_price,
			));
		
		}
	}
	
	function add_hidden_item($item_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		return $this->db->replace('grid_hidden_items',array('item_id' => $item_id,'location_id' => $location_id));
	}
	
	function remove_hidden_item($item_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location_id);
		
		return $this->db->delete('grid_hidden_items');
	}
	function is_item_hidden($item_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		$this->db->from('grid_hidden_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location_id);
		
		$query = $this->db->get();

		return ($query->num_rows()==1);
		
	}

	function add_ban_location($item_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		return $this->db->replace('location_ban_items',array('item_id' => $item_id,'location_id' => $location_id));
	}
	
	function remove_ban_location($item_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location_id);
		
		return $this->db->delete('location_ban_items');
	}

	
	function are_any_items_banned($location_id = NULL)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		$this->db->from('location_ban_items');
		$this->db->where('location_id',$location_id);
		
		$query = $this->db->get();

		return ($query->num_rows() > 0);
	}
	
	function get_banned_items($item_ids,$location_id = NULL)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		$item_ids_banned = array();
		
		
		$this->db->from('location_ban_items');
		$this->db->where('location_id',$location_id);
		if (!empty($item_ids))
		{
			$this->db->group_start();
			$item_ids_chunk = array_chunk($item_ids,25);
			foreach($item_ids_chunk as $item_ids)
			{
				$this->db->or_where_in('item_id',$item_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		
		foreach($this->db->get()->result_array() as $row)
		{
			$item_ids_banned[] = $row['item_id'];
		}
		
		
		$this->db->from('location_ban_tags');
		$this->db->join('tags','tags.id=location_ban_tags.tag_id');
		$this->db->join('items_tags','tags.id=items_tags.tag_id');
		$this->db->where('location_id',$location_id);
		if (!empty($item_ids))
		{
			$this->db->group_start();
			$item_ids_chunk = array_chunk($item_ids,25);
			foreach($item_ids_chunk as $item_ids)
			{
				$this->db->or_where_in('items_tags.item_id',$item_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		
		foreach($this->db->get()->result_array() as $row)
		{
			$item_ids_banned[] = $row['item_id'];
		}
		
		
		return array_unique($item_ids_banned);
		
	}

	function is_item_ban($item_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		$this->db->from('location_ban_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('location_id',$location_id);
		
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return TRUE;
		}
		
		$this->db->from('location_ban_tags');
		$this->db->join('tags','tags.id=location_ban_tags.tag_id');
		$this->db->join('items_tags','tags.id=items_tags.tag_id');
		$this->db->where('location_id',$location_id);
		$this->db->where('item_id',$item_id);
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	function save_damaged_qty($damaged_date,$damaged_qty,$damaged_reason,$item_id,$item_variation_id = NULL,$location_id = FALSE, $sale_id = NULL, $damaged_reason_comment = NULL)
	{
		if (!$location_id)
		{
			$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
		}
		
		$damaged_data = array(
			'damaged_date' => $damaged_date,
			'damaged_qty' => $damaged_qty,
			'damaged_reason' => $damaged_reason ? $damaged_reason : NULL,
			'item_id' => $item_id,
			'item_variation_id' => $item_variation_id,
			'location_id' => $location_id,
			'sale_id' => $sale_id,
			'damaged_reason_comment' => $damaged_reason_comment,
			
		);
		
		if ($sale_id)
		{
			//Check if damaged qty already exists and just update
			$this->db->from('damaged_items_log');
			$this->db->where('sale_id',$sale_id);
			$this->db->where('item_id',$item_id);
			$this->db->where('item_variation_id',$item_variation_id);
			
			$damaged_count = $this->db->count_all_results();
			
			if ($damaged_count != 0)
			{
				//Update
				$this->db->where('sale_id',$sale_id);
				$this->db->where('item_id',$item_id);
				$this->db->where('item_variation_id',$item_variation_id);
				
				$this->db->update('damaged_items_log',$damaged_data);
			}
			else
			{
				//Insert
				$this->db->insert('damaged_items_log',$damaged_data);
			}
			
		}
		else
		{
			$this->db->insert('damaged_items_log',$damaged_data);
		}
		
	}
	
	function delete_damaged_qty($sale_id,$item_id,$item_variation_id = NULL)
	{
		$this->db->where('sale_id',$sale_id);
		$this->db->where('item_id',$item_id);
		$this->db->where('item_variation_id',$item_variation_id);
		$this->db->delete('damaged_items_log');		
	}
	
	function get_secondary_categories($item_id)
	{
		$this->db->from('items_secondary_categories');
		$this->db->where('item_id',$item_id);
		return $this->db->get();
	}
	
	function save_secondory_category($item_id,$category_id,$sec_category_id = NULL)
	{
		if ($sec_category_id > 0)
		{
			$this->db->where('id',$sec_category_id);
			$this->db->update('items_secondary_categories',array('item_id' => $item_id,'category_id' => $category_id));
		}
		else
		{
			$this->db->replace('items_secondary_categories',array('item_id' => $item_id,'category_id' => $category_id));
		}
	}
	
	function delete_secondory_category($sec_category_id)
	{
		$this->db->where('id',$sec_category_id);
		$this->db->delete('items_secondary_categories');
	}

	function get_location_ban_item_list($location_id, $can_cache = TRUE){
		if($location_id == 0) return array();
		$ban_array = array();
		if($can_cache){
			static $cache = array();
		}else{
			$cache = array();
		}

		if(isset($cache[$location_id])){
			return $cache[$location_id];
		}

		$this->db->from('location_ban_items');
		$this->db->where('location_id',$location_id);
		foreach($this->db->get()->result_array() as $row)
		{
			array_push($ban_array,$row['item_id']);
		}
		$cache[$location_id] = $ban_array;
		return $ban_array;
	}

	function filter_location_item($result_array, $location_id = false, $can_cache = TRUE){

		if(count($result_array) == 0) return array();

		if($can_cache){
			static $see_all_items_1 = -1;
			static $location_id_1 = -1;

			if($see_all_items_1 === -1){
				$see_all_items_1 = $this->Employee->has_module_action_permission('items','see_all_items',$this->Employee->get_logged_in_employee_info()->person_id);
			}
			if($location_id_1 === -1){
				$location_id_1 = $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
			}
			$see_all_items = $see_all_items_1;
			if (!$location_id)
			{
				$location_id= $location_id_1;
			}	
		}else{
			$see_all_items = $this->Employee->has_module_action_permission('items','see_all_items',$this->Employee->get_logged_in_employee_info()->person_id);
			if (!$location_id)
			{
				$location_id= $this->Employee->get_logged_in_employee_current_location_id() ? $this->Employee->get_logged_in_employee_current_location_id() : 1;
			}			
		}

		if($see_all_items) return $result_array;
		$item_ids_in_search_result = array_column($result_array,'value');
		$item_ids_banned_in_search_result = $this->get_banned_items($item_ids_in_search_result,$location_id);

		$filter_array = array();
		for($iResult = 0; $iResult< count($result_array); $iResult ++){
			$eResult = $result_array[$iResult];
			if(!in_array($eResult['value'], $item_ids_banned_in_search_result)){
				array_push($filter_array, $eResult);
			}
		}
		return $filter_array;
	}
}
