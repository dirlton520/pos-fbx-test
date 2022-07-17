<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Items extends REST_Controller {
	
		protected $methods = [
        'index_get' => ['level' => 1, 'limit' => 20],
        'index_post' => ['level' => 2, 'limit' => 20],
        'index_delete' => ['level' => 2, 'limit' => 20],
        'batch_post' => ['level' => 2, 'limit' => 20],

      ];

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }
			
		private function _item_result_to_array($item)
		{
				$this->load->model('Category');
				$this->load->model('Manufacturer');
				$this->load->model('Tag');
				$this->load->model('Item');
				$this->load->model('Additional_item_numbers');
				$this->load->model('Item_serial_number');
				
				$manufacturers = array();
	
			 foreach($this->Manufacturer->get_all() as $id => $row)
			 {
					$manufacturers[$id] = $row['name'];
			 }
			  $additional_item_numbers = array();
			  $serial_numbers = array();
			  
			  foreach($this->Additional_item_numbers->get_item_numbers($item->item_id)->result_array() as $row)
			  {
			  	$additional_item_numbers[] = $row['item_number'];
			  }
			  
			  foreach($this->Item_serial_number->get_all($item->item_id) as $row)
			  {
			  	$serial_numbers[] = array('serial_number' => $row['serial_number'], 'unit_price' => $row['unit_price'] !== NULL ? to_currency_no_money($row['unit_price']) : NULL, 'cost_price' => $row['cost_price'] !== NULL ? to_currency_no_money($row['cost_price']) : NULL);
			  }
 			  $this->load->model('Item_modifier');
				
				
				$modifiers = array();
				require_once (APPPATH."models/cart/PHPPOSCartSale.php");
				
				foreach($this->Item_modifier->get_modifiers_for_item(new PHPPOSCartItemSale(array('scan' => $item->item_id.'|FORCE_ITEM_ID|','cart' => new PHPPOSCartSale())))->result_array() as $modifier)
				{
					$modifiers[] = (int)$modifier['id'];
				}

				$item_return = array(
					'item_id' => (int)$item->item_id,
					'name' => $item->name,
					'barcode_name' => $item->barcode_name,
					'modifiers' => $modifiers,
					'item_number' => $item->item_number,
					'supplier_id' => $item->supplier_id,
					'product_id' => $item->product_id,
					'size' => $item->size,
					'expire_days' => $item->expire_days,
					'ecommerce_product_id' => $item->ecommerce_product_id,
					'category' => $this->Category->get_full_path($item->category_id),
					'category_id' => $item->category_id  ? (int) $item->category_id : NULL,
					'manufacturer' => isset($manufacturers[$item->manufacturer_id]) ? $manufacturers[$item->manufacturer_id] : '',
					'manufacturer_id' => $item->manufacturer_id ? $item->manufacturer_id : NULL,
					'cost_price' => $item->cost_price !== NULL ? to_currency_no_money($item->cost_price) : NULL,
					'unit_price' => $item->unit_price !== NULL ? to_currency_no_money($item->unit_price) : NULL,
					'max_discount_percent' => $item->max_discount_percent !== NULL ? to_quantity($item->max_discount_percent,FALSE) : NULL,
					'max_edit_price' => $item->max_edit_price !== NULL ? to_currency_no_money($item->max_edit_price) : NULL,
					'min_edit_price' => $item->min_edit_price !== NULL ? to_currency_no_money($item->min_edit_price) : NULL,
					'promo_price' => $item->promo_price !== NULL ? to_currency_no_money($item->promo_price) : NULL,
					'start_date' => $item->start_date !== NULL ? date_as_display_date($item->start_date) : NULL,
					'end_date' => $item->end_date !== NULL ? date_as_display_date($item->end_date) : NULL,
					'reorder_level' => $item->reorder_level !== NULL ? to_quantity($item->reorder_level,FALSE) : NULL,
					'replenish_level' => $item->replenish_level !== NULL ? to_quantity($item->replenish_level,FALSE) : NULL,
					'description' => $item->description,
					'long_description' => $item->long_description,
					'disable_loyalty' => $item->disable_loyalty ? TRUE : FALSE,
					'is_service' => $item->is_service ? TRUE : FALSE,
					'allow_alt_description' => $item->allow_alt_description ? TRUE : FALSE,
					'is_serialized' => $item->is_serialized ? TRUE : FALSE,
					'is_favorite' => $item->is_favorite ? TRUE : FALSE,
					'is_ebt_item' => $item->is_ebt_item ? TRUE : FALSE,
					'is_ecommerce' => $item->is_ecommerce ? TRUE : FALSE,
					'tax_included' => $item->tax_included ? TRUE : FALSE,
					'change_cost_price' => $item->change_cost_price ? TRUE : FALSE,
					'override_default_tax' => $item->override_default_tax ? TRUE : FALSE,
					'tax_class_id' => $item->tax_class_id ? $item->tax_class_id : NULL,
					'tags' => $this->Tag->get_tags_for_item($item->item_id),
					'additional_item_numbers' => $additional_item_numbers,
					'serial_numbers' => $serial_numbers,
					'commission_percent' => $item->commission_percent !== NULL ? to_quantity($item->commission_percent,false) : NULL,
					'commission_fixed' => $item->commission_fixed !== NULL  ? to_currency_no_money($item->commission_fixed) : NULL,
					'commission_percent_type' => $item->commission_percent_type !== NULL ? $item->commission_percent_type : NULL,
					'allow_price_override_regardless_of_permissions' => $item->allow_price_override_regardless_of_permissions ? TRUE : FALSE,
					'only_integer' => $item->only_integer ? TRUE : FALSE,
					'is_barcoded' => $item->is_barcoded ? TRUE : FALSE,
					'item_inactive' => $item->item_inactive ? TRUE : FALSE,
					'main_image_id' => $item->main_image_id  ? (int) $item->main_image_id : NULL,
					'is_series_package'=> $item->is_series_package ? TRUE : FALSE,
					'series_quantity'=> $item->series_quantity ? (int)$item->series_quantity : NULL,
					'series_days_to_use_within'=> $item->series_days_to_use_within ? (int)$item->series_days_to_use_within : NULL,
					'default_quantity'=> $item->default_quantity ? (float)$item->default_quantity : NULL,
					'weight' => $item->weight ? to_quantity($item->weight) : NULL,
					'length' => $item->length ? to_quantity($item->length) : NULL,
					'width' => $item->width ? to_quantity($item->width) : NULL,
					'height' => $item->height ? to_quantity($item->height) : NULL,
					'info_popup' => $item->info_popup ? $item->info_popup : NULL,
					'last_modified' => $item->last_modified !== NULL ? date_as_display_date($item->last_modified) : NULL,
					'loyalty_multiplier' => $item->loyalty_multiplier ? $item->loyalty_multiplier : NULL,
				);
				
	
				for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
				{
					if($this->Item->get_custom_field($k) !== false)
					{
						$field = array();
						$field['label']= $this->Item->get_custom_field($k);
						if($this->Item->get_custom_field($k,'type') == 'date')
						{
							$field['value'] = date_as_display_date($item->{"custom_field_{$k}_value"});
						}
						else
						{
							$field['value'] = $item->{"custom_field_{$k}_value"};
						}
						
						$item_return['custom_fields'][$field['label']] = $field['value'];
					}
				}
				
				$item_return['images'] = array();
				
				foreach($this->Item->get_item_images($item->item_id) as $image)
				{
					$item_return['images'][] = array('image_url' => secure_app_file_url($image['image_id']),'title' => $image['title'],'alt_text' => $image['alt_text'],'variation_id' => $image['item_variation_id']);
				}
				
				$item_return['variations'] = array();
				
				$this->load->model('Item_variations');
				foreach($this->Item_variations->get_all($item->item_id) as $variation)
				{
					$var_attributes = $this->Item_variations->get_attributes($variation['id']);
					
					$attributes = array();
					foreach($var_attributes as $var_attribute)
					{
						$attributes[] = array('name' => $var_attribute['attribute_name'],'value' =>$var_attribute['attribute_value_name']);
					}
					
					$variation_additional_item_numbers = array();
				  foreach($this->Additional_item_numbers->get_item_numbers_for_variation($item->item_id,$variation['id'])->result_array() as $row)
				  {
				  	$variation_additional_item_numbers[] = $row['item_number'];
				  }
					
					
					$item_return['variations'][] = array(
						'variation_id' => (int)$variation['id'],
						'name' => $variation['name'],
						'item_number'=>$variation['item_number'],
						'additional_item_numbers' => $variation_additional_item_numbers,
						'unit_price' => to_currency_no_money($variation['unit_price']),
						'promo_price' => to_currency_no_money($variation['promo_price']),
						'start_date' => $variation['start_date'] !== NULL ? date_as_display_date($variation['start_date']) : NULL,
						'end_date' => $variation['end_date'] !== NULL ? date_as_display_date($variation['end_date']) : NULL,
						'reorder_level' => $variation['reorder_level'] !== NULL ? to_quantity($variation['reorder_level'],FALSE) : NULL,
						'replenish_level' => $variation['replenish_level'] !== NULL ? to_quantity($variation['replenish_level'],FALSE) : NULL,
						'cost_price' => to_currency_no_money($variation['cost_price']),
						'attributes' => $attributes
					);
				}
				
				$this->load->model('Tier');
				$item_return['tier_pricing'] = array();
			  foreach($this->Tier->get_all()->result_array() as $tier)
			  {
					$tier_id = $tier['id'];
					$tier_name = $tier['name'];
					$tier_price_row = $this->Item->get_tier_price_row($tier_id,$item->item_id);
					
					if ($tier_price_row)
					{
						if ($tier_price_row->unit_price !== NULL)
						{
							$tier_type = 'unit_price';
							$tier_value = to_currency_no_money($tier_price_row->unit_price,10);
						
						}
						elseif($tier_price_row->percent_off !== NULL)
						{
							$tier_type = 'percent_off';		
							$tier_value = to_quantity($tier_price_row->percent_off,false);						
													
						}
						elseif($tier_price_row->cost_plus_percent !== NULL)
						{
							$tier_type = 'cost_plus_percent';		
							$tier_value = to_quantity($tier_price_row->cost_plus_percent,false);						
																						
						}
						elseif($tier_price_row->cost_plus_fixed_amount !== NULL)
						{
							$tier_type = 'cost_plus_fixed_amount';
							$tier_value = to_currency_no_money($tier_price_row->cost_plus_fixed_amount,10);						
						}
						else
						{
							$tier_type = NULL;
						}
			
						if ($tier_type !== NULL)
						{
				  		$item_return['tier_pricing'][] = array('name' => $tier_name, 'value' => $tier_value,'type' => $tier_type);
						}
					}
				}
				
				$item_return['locations'] = array();
				
				$this->load->model('Location');
				$this->load->model('Item_location');
				$this->load->helper('date');
				$this->load->model('Item_attribute');
				$this->load->model('Item_attribute_value');
				$this->load->model('Item_variations');
				$this->load->model('Item_variation_location');
				
				foreach($this->Location->get_all()->result_array() as $location_row)
				{
					$item_loc_row = array();
					$item_location_info = $this->Item_location->get_info($item->item_id,$location_row['location_id']);
					$item_loc_row['quantity'] = to_quantity($item_location_info->quantity,FALSE);
					$item_loc_row['location'] = $item_location_info->location;
					$item_loc_row['unit_price'] = to_currency_no_money($item_location_info->unit_price);
					$item_loc_row['cost_price'] = to_currency_no_money($item_location_info->cost_price);
					$item_loc_row['promo_price'] = to_currency_no_money($item_location_info->promo_price);
					$item_loc_row['start_date'] = $item_location_info->start_date !== NULL ? date_as_display_date($item_location_info->start_date) : NULL;
					$item_loc_row['end_date'] = $item_location_info->end_date !== NULL ? date_as_display_date($item_location_info->end_date) : NULL;
					$item_loc_row['reorder_level'] = to_quantity($item_location_info->reorder_level, FALSE);
					$item_loc_row['replenish_level'] = to_quantity($item_location_info->replenish_level, FALSE);
					$item_loc_row['override_default_tax'] = $item_location_info->override_default_tax ? TRUE : FALSE;
					$item_loc_row['tax_class_id'] = $item_location_info->tax_class_id ? $item_location_info->tax_class_id : NULL;
					
					$item_loc_row['variations'] = array();
					foreach($this->Item_variations->get_all($item->item_id) as $variation)
					{
						$item_variation_id = $variation['id'];
						$item_var_loc_info = $this->Item_variation_location->get_info($item_variation_id,$location_row['location_id']);
						$item_var_loc_row = array();
						$item_var_loc_row['variation_id'] = (int)$item_variation_id;
						$item_var_loc_row['reorder_level'] = $item_var_loc_info->reorder_level !== NULL ? to_quantity($item_var_loc_info->reorder_level, FALSE) : NULL;
						$item_var_loc_row['replenish_level'] = $item_var_loc_info->replenish_level !== NULL ? to_quantity($item_var_loc_info->replenish_level, FALSE) : NULL;
						$item_var_loc_row['quantity'] = $item_var_loc_info->quantity !== NULL ? to_quantity($item_var_loc_info->quantity, FALSE) : NULL;
						$item_var_loc_row['unit_price'] = to_currency_no_money($item_var_loc_info->unit_price);
						$item_var_loc_row['cost_price'] = to_currency_no_money($item_var_loc_info->cost_price);
						
						$item_loc_row['variations'][] = $item_var_loc_row;		
					
					}
					
					$item_loc_row['tier_pricing'] = array();
				  foreach($this->Tier->get_all()->result_array() as $tier)
				  {
						$tier_id = $tier['id'];
						$tier_name = $tier['name'];
						$tier_price_row = $this->Item_location->get_tier_price_row($tier_id,$item->item_id,$location_row['location_id']);
					
						if ($tier_price_row)
						{
							if ($tier_price_row->unit_price !== NULL)
							{
								$tier_type = 'unit_price';
								$tier_value = to_currency_no_money($tier_price_row->unit_price,10);
						
							}
							elseif($tier_price_row->percent_off !== NULL)
							{
								$tier_type = 'percent_off';		
								$tier_value = to_quantity($tier_price_row->percent_off,false);						
													
							}
							elseif($tier_price_row->cost_plus_percent !== NULL)
							{
								$tier_type = 'cost_plus_percent';		
								$tier_value = to_quantity($tier_price_row->cost_plus_percent,false);						
																						
							}
							elseif($tier_price_row->cost_plus_fixed_amount !== NULL)
							{
								$tier_type = 'cost_plus_fixed_amount';
								$tier_value = to_currency_no_money($tier_price_row->cost_plus_fixed_amount,10);						
							}
							else
							{
								$tier_type = NULL;
							}
			
							if ($tier_type !== NULL)
							{
					  		$item_loc_row['tier_pricing'][] = array('name' => $tier_name, 'value' => $tier_value,'type' => $tier_type);
							}
						}
					}
					
					$item_return['locations'][$location_row['location_id']] = $item_loc_row;
				}

				$item_return['unit_variations'] = array();
				foreach($this->Item->get_quantity_units($item->item_id) as $unit){
					$item_return['unit_variations'][] = array(
						'id' => $unit->id,
						'unit_name' => $unit->unit_name,
						'unit_quantity' => $unit->unit_quantity,
						'unit_price' => $unit->unit_price,
						'cost_price' => $unit->cost_price,
						'quantity_unit_item_number' => $unit->quantity_unit_item_number
					);
				}
				
				return $item_return;
		}

		public function index_delete($item_id)
		{
			$this->load->model('Item');

			if ($item_id === NULL || !is_numeric($item_id))
      {
      		$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
			}
			  $item = $this->Item->get_info($item_id);
      	if ($item->item_id && !$item->deleted)
				{	
						$this->Item->delete($item_id);
				    $item_return = $this->_item_result_to_array($item);
						$this->response($item_return, REST_Controller::HTTP_OK);
				}
				else
				{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
				}
			
		}
				
    public function index_get($item_id = NULL)
    {
			$this->load->model('Item');
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($item_id === NULL)
      {
      	$search = $this->input->get('search');
      	$search_field = $this->input->get('search_field');
				$offset = $this->input->get('offset');
				$limit = $this->input->get('limit');
				
				if ($limit !== NULL && $limit > 100)
				{
					$limit = 100;
				}

				
				if ($search || $this->input->get('category_id'))
				{
					if ($search_field !== NULL)
					{
							$search_field_map = array(
							'item_id' => $this->db->dbprefix('items').'.item_id',
							'item_number' => $this->db->dbprefix('items').'.item_number',
							'ecommerce_product_id' => $this->db->dbprefix('items').'.ecommerce_product_id',
							'product_id' => $this->db->dbprefix('items').'.product_id',
							'name' => $this->db->dbprefix('items').'.name',
							'size' => $this->db->dbprefix('items').'.size',
							'description' => $this->db->dbprefix('items').'.description',
							'cost_price' => $this->db->dbprefix('items').'.cost_price',
							'unit_price' => $this->db->dbprefix('items').'.unit_price',
							'promo_price' => $this->db->dbprefix('items').'.promo_price',
							'reorder_level' => $this->db->dbprefix('items').'.reorder_level',
							'manufacturer_name' => $this->db->dbprefix('manufacturers').'.name',
							'supplier' => $this->db->dbprefix('suppliers').'.company_name',
							'tag_name' => $this->db->dbprefix('tags').'.name',
							);

						
						$custom_fields_map = array();
			
						for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
						{
							if($this->Item->get_custom_field($k) !== false)
							{
								$custom_fields_map[$this->Item->get_custom_field($k)] = $this->db->dbprefix('items').".custom_field_${k}_value";
							}
						}
						
						if (isset($search_field_map[$search_field]))
						{
							$search_field = $search_field_map[$search_field];
						}
						if (isset($custom_fields_map[$search_field]))
						{
							$search_field = $custom_fields_map[$search_field];
						}
						elseif (strpos($search_field, 'custom_field') !== false)
						{
							$search_field = $this->db->dbprefix('items').'.'.$search_field;
						}
					}
					
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$items = $this->Item->search($search, 0, $this->input->get('category_id') ? $this->input->get('category_id') : FALSE,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$search_field ? $search_field : 'all')->result();
					$total_records = $this->Item->count_last_query_results();
				}
				else
				{
					
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$items = $this->Item->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result();
					$total_records = $this->Item->count_last_query_results();
				}
				
				$items_return = array();
				foreach($items as $item)
				{
						$items_return[] = $this->_item_result_to_array($item);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($items_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
      			if (!is_numeric($item_id))
      			{
							$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
      			}
      			
        		$item = $this->Item->get_info($item_id);
        		
        		if ($item->item_id)
        		{
        			$item_return = $this->_item_result_to_array($item);
							$this->response($item_return, REST_Controller::HTTP_OK);
					}
					else
					{
							$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
    
    public function index_post($item_id = NULL)
    {
			if ($item_id!== NULL)
			{
				$this->_update($item_id);
				return;
			}
			
    	$this->load->model('Item');
			if (isset($_FILES["images"]["tmp_name"][0]))
			{
				$item_request = json_decode($_POST['item'],TRUE);
			}
			else
			{
				$item_request = json_decode(file_get_contents('php://input'),TRUE);
			}
			
			if ($item_id = $this->_create_item($item_request))
			{
				$item_return = $this->_item_result_to_array($this->Item->get_info($item_id));
				$this->response($item_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
    
    private function _populate_custom_fields($item_request,&$item_data)
    {
    	$custom_fields_map = array();
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				if($this->Item->get_custom_field($k) !== false)
				{
					$custom_fields_map[$this->Item->get_custom_field($k)] = array('index' => $k, 'type' => $this->Item->get_custom_field($k,'type'));
				}

			}
			if (isset($item_request['custom_fields']))
			{
				foreach($item_request['custom_fields'] as $custom_field => $custom_field_value)
				{
					if(isset($custom_fields_map[$custom_field]))
					{
						$key = $custom_fields_map[$custom_field]['index'];
						$type = $custom_fields_map[$custom_field]['type'];
					
						if ($type == 'date')
						{
							$item_data["custom_field_{$key}_value"] = strtotime($custom_field_value);
						}
						else
						{
							$item_data["custom_field_{$key}_value"] = $custom_field_value;
						}
					}
				}
			}
    }
		
		public function _save_item_location_data($location_data,$item_id)
		{
			$this->load->model('Item_location');
			$this->load->model('Item_variation_location');
			foreach($location_data as $location_id=>$item_location_info)
			{
				$item_location_data = array();
				
				if (isset($item_location_info['quantity']))
				{
					$item_location_data['quantity'] = $item_location_info['quantity'];
					
					$cur_item_location_info = $this->Item_location->get_info($item_id,$location_id);
				
					if ($cur_item_location_info->quantity != $item_location_data['quantity'])
						{
						$inv_data = array
							(
							'trans_date'=>date('Y-m-d H:i:s'),
							'trans_items'=>$item_id,
							'trans_user'=>1,
							'trans_comment'=>'API',
							'trans_inventory'=>$item_location_data['quantity'] - (float)$cur_item_location_info->quantity,
							'location_id'=>$location_id,
							'trans_current_quantity' => $item_location_data['quantity'],
						);
						$this->load->model('Inventory');
						$this->Inventory->insert($inv_data);
						
					}
				}

				if (isset($item_location_info['location']))
				{
					$item_location_data['location'] = $item_location_info['location'];
				}
				
				if (isset($item_location_info['unit_price']))
				{
					$item_location_data['unit_price'] = $item_location_info['unit_price'];
				}

				if (isset($item_location_info['cost_price']))
				{
					$item_location_data['cost_price'] = $item_location_info['cost_price'];
				}

				if (isset($item_location_info['promo_price']))
				{
					$item_location_data['promo_price'] = $item_location_info['promo_price'];
				}

				if (isset($item_location_info['start_date']))
				{
					$item_location_data['start_date'] = date('Y-m-d',strtotime($item_location_info['start_date']));
				}
				if (isset($item_location_info['end_date']))
				{
					$item_location_data['end_date'] = date('Y-m-d',strtotime($item_location_info['end_date']));
				}
				
				if (isset($item_location_info['reorder_level']))
				{
					$item_location_data['reorder_level'] = $item_location_info['reorder_level'];
				}

				if (isset($item_location_info['replenish_level']))
				{
					$item_location_data['replenish_level'] = $item_location_info['replenish_level'];
				}
				
				if (isset($item_location_info['override_default_tax']))
				{
					$item_location_data['override_default_tax'] = $item_location_info['override_default_tax'] ? 1 : 0;
				}
				
				if (isset($item_location_info['tax_class_id']))
				{
					$item_location_data['tax_class_id'] = $item_location_info['tax_class_id'];
				}
					
				$this->Item_location->save($item_location_data,$item_id,$location_id);
						
						
				if (isset($item_location_info['variations']))
				{
					foreach($item_location_info['variations'] as $variation_data)
					{
						$item_variation_location_data = array();
						$item_variation_id = $variation_data['variation_id'];
					
						if(isset($variation_data['reorder_level']))
						{
							$item_variation_location_data['reorder_level'] = $variation_data['reorder_level'];
						}
					
						if(isset($variation_data['replenish_level']))
						{
							$item_variation_location_data['replenish_level'] = $variation_data['replenish_level'];
						}
						
						if(isset($variation_data['cost_price']))
						{
							$item_variation_location_data['cost_price'] = $variation_data['cost_price'];
						}
						
						if(isset($variation_data['unit_price']))
						{
							$item_variation_location_data['unit_price'] = $variation_data['unit_price'];
						}
						
						if(isset($variation_data['quantity']))
						{
							$item_variation_location_data['quantity'] = $variation_data['quantity'];
							
							$cur_item_var_location_info = $this->Item_variation_location->get_info($item_variation_id,$location_id);
				
							if ($cur_item_var_location_info->quantity != $item_variation_location_data['quantity'])
								{
								$inv_data = array
									(
									'trans_date'=>date('Y-m-d H:i:s'),
									'trans_items'=>$item_id,
									'item_variation_id'=>$item_variation_id,
									'trans_user'=>1,
									'trans_comment'=>'API',
									'trans_inventory'=>$item_variation_location_data['quantity'] - $cur_item_var_location_info->quantity,
									'location_id'=>$location_id,
									'trans_current_quantity' => $item_variation_location_data['quantity'],
								);
								$this->load->model('Inventory');
								$this->Inventory->insert($inv_data);
							}
						}
					
						$this->Item_variation_location->save($item_variation_location_data, $item_variation_id, $location_id);
					}
				}
				
				
				if (isset($item_location_info['tier_pricing']))
				{
					$this->load->model('Tier');
					foreach($item_location_info['tier_pricing'] as $tier_data)
					{
						$tier_info = $this->Tier->get_info_by_name($tier_data['name']);
				
						//Couldn't find tier
						if (!$tier_info->id)
						{
							continue;
						}
				
						$price_or_percent = $tier_data['value'];
						$tier_type = $tier_data['type'];
						$tier_id = $tier_info->id;
				
						if ($price_or_percent)
						{				
							$tier_data=array('tier_id'=>$tier_id);
							$tier_data['item_id'] = $item_id;

							if ($tier_type == 'unit_price')
							{
								$tier_data['unit_price'] = $price_or_percent;
								$tier_data['percent_off'] = NULL;
								$tier_data['cost_plus_percent'] = NULL;
								$tier_data['cost_plus_fixed_amount'] = NULL;
							}
							elseif($tier_type == 'percent_off')
							{
								$tier_data['percent_off'] = (float)$price_or_percent;
								$tier_data['unit_price'] = NULL;
								$tier_data['cost_plus_percent'] = NULL;
								$tier_data['cost_plus_fixed_amount'] = NULL;
							}
							elseif($tier_type == 'cost_plus_percent')
							{
								$tier_data['percent_off'] = NULL;
								$tier_data['unit_price'] = NULL;
								$tier_data['cost_plus_percent'] = (float)$price_or_percent;
								$tier_data['cost_plus_fixed_amount'] = NULL;
							}
							elseif($tier_type == 'cost_plus_fixed_amount')
							{
								$tier_data['percent_off'] = NULL;
								$tier_data['unit_price'] = NULL;
								$tier_data['cost_plus_percent'] = NULL;
								$tier_data['cost_plus_fixed_amount'] = (float)$price_or_percent;
							}
			
							$this->Item_location->save_item_tiers($tier_data,$item_id,$location_id);
						}
						else
						{
							$this->Item_location->delete_tier_price($tier_id, $item_id,$location_id);
						}
					}
				}
				else
				{
					$this->Item_location->delete_all_tier_prices($item_id,$location_id);
				}
			}
		}
		
		public function _save_item_quantity_unit_variations($item_request,$item_id)
		{
			$this->load->model('Item');
			if (isset($item_request['unit_variations']))
			{
				foreach($item_request['unit_variations'] as $unit_variation){
					$unit_variation_data = array(
						"item_id" => $item_id,
						"unit_name" => $unit_variation["unit_name"],
						"unit_quantity" => $unit_variation["unit_quantity"],
						"unit_price" => $unit_variation["unit_price"],
						"cost_price" => $unit_variation["cost_price"],
						"quantity_unit_item_number" => $unit_variation["quantity_unit_item_number"],
					);

					if(isset($unit_variation['id'])){
						$this->Item->save_unit_quantity($unit_variation_data, $unit_variation['id']);
					}else{
						$this->Item->save_unit_quantity($unit_variation_data);
					}
				}
			}
		}
		
		public function _save_item_variations($item_request,$item_id)
		{
			$this->load->model('Item_attribute');
			$this->load->model('Item_attribute_value');
			$this->load->model('Item_variations');
			$this->load->model('Item_variation_location');
			
			if (isset($item_request['variations']))
			{
					$variations = $item_request['variations'];					
					foreach($variations as $variation)
					{
						$variation_id = isset($variation['variation_id']) ? $variation['variation_id'] : FALSE;
						$variation_attributes = $variation['attributes'];
						$variation_attribute_value_ids = array();
						
						foreach($variation_attributes as $attribute)
						{
							$attr_name = $attribute['name'];
							$attr_value = $attribute['value'];
							
							if (!$this->Item_attribute->attribute_name_exists($attr_name) && !$this->Item_attribute->attribute_name_exists($attr_name,$item_id))
							{
								$attribute_data = array('name' => $attr_name,'item_id' => $item_id);
								$this->Item_attribute->save($attribute_data);
								$attr_id = $attribute_data['id'];
							}
							else
							{
								$attr_id = $this->Item_attribute->get_info($attr_name,FALSE)->id;
								if (!$attr_id)
								{
									$attr_id = $this->Item_attribute->get_info($attr_name, FALSE, $item_id)->id;
								}
							}
							
							$attr_value_id = $this->Item_attribute_value->save($attr_value,$attr_id);
							$this->Item_attribute_value->save_item_attribute_values($item_id,array($attr_value_id));
							$variation_attribute_value_ids[] = $attr_value_id;
						}
						
						$variation_data = array();
						
						$variation_data['item_id'] = $item_id;
						if(isset($variation['name']) && $variation['name'])
						{
							$variation_data['name'] = $variation['name'];
						}

						if(isset($variation['item_number']) && $variation['item_number'])
						{
							$variation_data['item_number'] = $variation['item_number'];
						}
						
						if(isset($variation['replenish_level']))
						{
							$variation_data['replenish_level'] = $variation['replenish_level'];
						}

						if(isset($variation['reorder_level']))
						{
							$variation_data['reorder_level'] = $variation['reorder_level'];
						}

						if(isset($variation['unit_price']))
						{
							$variation_data['unit_price'] = $variation['unit_price'];
						}

						if(isset($variation['cost_price']))
						{
							$variation_data['cost_price'] = $variation['cost_price'];
						}

						if(isset($variation['promo_price']))
						{
							$variation_data['promo_price'] = $variation['promo_price'];
						}
						
						if(isset($variation['start_date']))
						{
							$variation_data['start_date'] = date('Y-m-d',strtotime($variation['start_date']));
						}

						if(isset($variation['end_date']))
						{
							$variation_data['end_date'] = date('Y-m-d',strtotime($variation['end_date']));
						}
						
						$item_variation_id = $variation_id ? $variation_id : $this->Item_variations->lookup($item_id, $variation_attribute_value_ids);
						$item_variation_id = $this->Item_variations->save($variation_data,$item_variation_id, $variation_attribute_value_ids); 
						
						
						if (isset($variation['additional_item_numbers']))
						{
							$this->load->model('Additional_item_numbers');
							if (!empty($variation['additional_item_numbers']))
							{
								$this->Additional_item_numbers->save_variation($item_id,$item_variation_id,$variation['additional_item_numbers']);
							}
							else
							{
								$this->Additional_item_numbers->delete_variation($item_id,$item_variation_id);
							}
						}
						
						
						if(isset($variation['image_url']) && $variation['image_url'])
						{		
							$this->load->library('image_lib');
							@$image_contents = file_get_contents_curl($variation['image_url']);
							$tmpFilename = tempnam(ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir(), 'api');
							file_put_contents($tmpFilename,$image_contents);

							$config['image_library'] = 'gd2';
							$config['source_image']	= $tmpFilename;
							$config['create_thumb'] = FALSE;
							$config['maintain_ratio'] = TRUE;
							$config['width']	 = 1200;
							$config['height']	= 900;
							$this->image_lib->initialize($config);
							$this->image_lib->resize();
							$this->load->model('Appfile');
							$image_contents = file_get_contents($tmpFilename);
							if ($image_contents)
							{
								$image_file_id = $this->Appfile->save(basename($variation['image_url']), $image_contents);
								$this->Item->add_image($item_id, $image_file_id);
						
								$title = isset($variation['image_title']) ? $variation['image_title'] : '';
								$alt_text = isset($variation['image_alt_text']) ? $variation['image_alt_text'] : '';
								$this->Item->save_image_metadata($image_file_id, $title, $alt_text, $item_variation_id);
							}
						}
						
					}
			}
		}
    
    private function _create_item($item_request)
    {
    	 $this->load->model('Item');
    	 $this->load->model('Tag');
			 $this->load->model('Category');
			 $this->load->model('Item_modifier');
			 
			 $category_name = isset($item_request['category']) ? $item_request['category'] : NULL;
			 $item_data = array(
						'name'=>isset($item_request['name']) ? $item_request['name'] :  '',
						'barcode_name'=>isset($item_request['barcode_name']) ? $item_request['barcode_name'] :  '',
						'description'=>isset($item_request['description']) ? $item_request['description'] :  '',
						'long_description'=>isset($item_request['long_description']) ? $item_request['long_description'] :  '',
						'size'=>isset($item_request['size']) ? $item_request['size'] :  '',
						'expire_days'=>isset($item_request['expire_days']) ? $item_request['expire_days'] :  NULL,
						'item_number'=>isset($item_request['item_number']) ? $item_request['item_number'] :  NULL,
						'product_id'=>isset($item_request['product_id']) ? $item_request['product_id'] :  NULL,
						'category_id'=>isset($item_request['category_id']) ? $item_request['category_id'] :  $this->Category->get_category_id($category_name),
						'manufacturer_id'=>isset($item_request['manufacturer_id']) ? $item_request['manufacturer_id'] : NULL,
						'supplier_id'=>isset($item_request['supplier_id']) ? $item_request['supplier_id'] : NULL,
						'unit_price'=>isset($item_request['unit_price']) ? $item_request['unit_price'] : 0,
						'max_discount_percent'=>isset($item_request['max_discount_percent']) ? $item_request['max_discount_percent'] : NULL,
						'max_edit_price'=>isset($item_request['max_edit_price']) ? $item_request['max_edit_price'] : NULL,
						'min_edit_price'=>isset($item_request['min_edit_price']) ? $item_request['min_edit_price'] : NULL,
						'promo_price'=>isset($item_request['promo_price']) ? $item_request['promo_price'] : NULL,
						'start_date'=>isset($item_request['start_date']) ? date('Y-m-d',strtotime($item_request['start_date'])) : NULL,
						'end_date'=>isset($item_request['end_date']) ? date('Y-m-d',strtotime($item_request['end_date'])) : NULL,
						'cost_price'=>isset($item_request['cost_price']) ? $item_request['cost_price'] : 0,
						'disable_loyalty'=>isset($item_request['disable_loyalty']) && $item_request['disable_loyalty'] ? 1 : 0,
						'tax_included'=>isset($item_request['tax_included']) && $item_request['tax_included'] ? 1 : 0,
						'is_service'=>isset($item_request['is_service']) && $item_request['is_service'] ? 1 : 0,
						'change_cost_price'=>isset($item_request['change_cost_price']) && $item_request['change_cost_price'] ? 1 : 0,
						'override_default_tax'=>isset($item_request['override_default_tax']) && $item_request['override_default_tax'] ? 1 : 0,
						'tax_class_id'=>isset($item_request['tax_class_id']) ? $item_request['tax_class_id'] : NULL,
						'commission_percent' => isset($item_request['commission_percent']) ? $item_request['commission_percent'] : NULL,
						'commission_fixed' => isset($item_request['commission_fixed']) ? $item_request['commission_fixed'] : NULL,
						'commission_percent_type' => isset($item_request['commission_percent_type']) ? $item_request['commission_percent_type'] : NULL,
						'is_favorite' => isset($item_request['is_favorite']) ? $item_request['is_favorite'] : 0,
						'weight' => isset($item_request['weight']) ? $item_request['weight'] : NULL,
						'length' => isset($item_request['length']) ? $item_request['length'] : NULL,
						'width' => isset($item_request['width']) ? $item_request['width'] : NULL,
						'height' => isset($item_request['height']) ? $item_request['height'] : NULL,
						'info_popup' => isset($item_request['info_popup']) ? $item_request['info_popup'] : NULL,
						'allow_price_override_regardless_of_permissions'=>isset($item_request['allow_price_override_regardless_of_permissions']) && $item_request['allow_price_override_regardless_of_permissions'] ? 1 : 0,
						'main_image_id' => isset($item_request['main_image_id']) ? $item_request['main_image_id'] : NULL,
						'only_integer'=>isset($item_request['only_integer']) && $item_request['only_integer'] ? 1 : 0,
						'is_barcoded'=>isset($item_request['is_barcoded']) && $item_request['is_barcoded'] ? 1 : 0,
						'item_inactive' => isset($item_request['item_inactive']) && $item_request['item_inactive'] ? 1 : 0,
						'is_series_package'=>isset($item_request['is_series_package']) && $item_request['is_series_package'] ? 1 : 0,
						'series_quantity'=>isset($item_request['series_quantity']) && $item_request['series_quantity'] ? $item_request['series_quantity'] : NULL,
						'series_days_to_use_within'=>isset($item_request['series_days_to_use_within']) && $item_request['series_days_to_use_within'] ? $item_request['series_days_to_use_within'] : NULL,
						'default_quantity'=>isset($item_request['default_quantity']) && $item_request['default_quantity'] ? $item_request['default_quantity'] : NULL,
						'loyalty_multiplier'=>isset($item_request['loyalty_multiplier']) && $item_request['loyalty_multiplier'] ? $item_request['loyalty_multiplier'] : NULL,
					);
		

			$this->_populate_custom_fields($item_request,$item_data);
			$this->Item->save($item_data);
			$this->_save_and_populate_images($item_request,$item_data['item_id']);
			$this->_save_item_variations($item_request,$item_data['item_id']);
			$this->_save_item_quantity_unit_variations($item_request,$item_data['item_id']);
			
			if (isset($item_request['modifiers']))
			{
				$this->Item_modifier->item_save_modifiers($item_data['item_id'],$item_request['modifiers']);
			}
			if (isset($item_request['locations']))
			{
				$this->_save_item_location_data($item_request['locations'],$item_data['item_id']);
			}
			
			if (isset($item_request['tags']) && $item_request['tags'])
			{
				$this->Tag->save_tags_for_item($item_data['item_id'] , implode(',',$item_request['tags']));
			}
			
			
			if (isset($item_request['additional_item_numbers']))
			{
				$this->load->model('Additional_item_numbers');
				if (!empty($item_request['additional_item_numbers']))
				{
					$this->Additional_item_numbers->save($item_data['item_id'], $item_request['additional_item_numbers']);
				}
				else
				{
					$this->Additional_item_numbers->delete($item_data['item_id']);
				}
			}
			
			if (isset($item_request['serial_numbers']))
			{
				$this->load->model('Item_serial_number');
				if (!empty($item_request['serial_numbers']))
				{
					$this->Item_serial_number->save($item_data['item_id'], array_column($item_request['serial_numbers'],'serial_number'), array_column($item_request['serial_numbers'],'cost_price'), array_column($item_request['serial_numbers'],'unit_price'), array_column($item_request['serial_numbers'],'variation_id'));
				}
				else
				{
					$this->Item_serial_number->delete($item_data['item_id']);
				}
			}
			
			if (isset($item_request['tier_pricing']))
			{
				if (!empty($item_request['tier_pricing']))
				{
					$this->load->model('Tier');
					foreach($item_request['tier_pricing'] as $tier_data)
					{
						$tier_info = $this->Tier->get_info_by_name($tier_data['name']);
						
						//Couldn't find tier
						if (!$tier_info->id)
						{
							continue;
						}
						
						$price_or_percent = $tier_data['value'];
						$tier_type = $tier_data['type'];
						$tier_id = $tier_info->id;
						
						if ($price_or_percent)
						{				
							$tier_data=array('tier_id'=>$tier_id);
							$tier_data['item_id'] = $item_data['item_id'];

							if ($tier_type == 'unit_price')
							{
								$tier_data['unit_price'] = $price_or_percent;
								$tier_data['percent_off'] = NULL;
								$tier_data['cost_plus_percent'] = NULL;
								$tier_data['cost_plus_fixed_amount'] = NULL;
							}
							elseif($tier_type == 'percent_off')
							{
								$tier_data['percent_off'] = (float)$price_or_percent;
								$tier_data['unit_price'] = NULL;
								$tier_data['cost_plus_percent'] = NULL;
								$tier_data['cost_plus_fixed_amount'] = NULL;
							}
							elseif($tier_type == 'cost_plus_percent')
							{
								$tier_data['percent_off'] = NULL;
								$tier_data['unit_price'] = NULL;
								$tier_data['cost_plus_percent'] = (float)$price_or_percent;
								$tier_data['cost_plus_fixed_amount'] = NULL;
							}
							elseif($tier_type == 'cost_plus_fixed_amount')
							{
								$tier_data['percent_off'] = NULL;
								$tier_data['unit_price'] = NULL;
								$tier_data['cost_plus_percent'] = NULL;
								$tier_data['cost_plus_fixed_amount'] = (float)$price_or_percent;
							}
					
							$this->Item->save_item_tiers($tier_data,$item_data['item_id']);
						}
						else
						{
							$this->Item->delete_tier_price($tier_id, $item_data['item_id']);
						}
					}
				}
				else
				{
					$this->Item->delete_all_tier_prices($item_data['item_id']);
				}
			}
			
			return $item_data['item_id'];
    }
    
    private function _update_item($item_id,$item_request)
    {
   	  $this->load->model('Item');
   	  $this->load->model('Item_modifier');

			$item_data = array();
			
    	foreach($item_request as $key=>$value)
    	{
					if ($key == 'modifiers')
					{
						$this->Item_modifier->item_save_modifiers($item_id,$value);
					}
					elseif ($key == 'category')
					{
						$this->load->model('Category');
						$item_data['category_id'] = $this->Category->get_category_id($value);
					}
					elseif($key == 'variations')
					{
						$this->_save_item_variations($item_request,$item_id);
					}
					elseif($key == 'unit_variations')
					{
						$this->_save_item_quantity_unit_variations($item_request,$item_id);
					}
    			elseif ($key=="tags")
    			{
    			  $this->load->model('Tag');
						$this->Tag->save_tags_for_item($item_id , implode(',',$value));
    			}
    			elseif($key=='additional_item_numbers')
    			{
						$this->load->model('Additional_item_numbers');
						if (!empty($value))
						{
							$this->Additional_item_numbers->save($item_id, $value);
						}
						else
						{
							$this->Additional_item_numbers->delete($item_id);
						}
    			}
    			elseif($key=='serial_numbers')
    			{
						$this->load->model('Item_serial_number');
						if (!empty($value))
						{
							$this->Item_serial_number->save($item_id, array_column($value,'serial_number'), array_column($value,'cost_price'), array_column($value,'unit_price'), array_column($value,'variation_id'));
						}
						else
						{
							$this->Item_serial_number->delete($item_id);
						}    			
    			}
					elseif($key == 'tier_pricing')
					{
						if (!empty($value))
						{
							$this->load->model('Tier');
							foreach($value as $tier_data)
							{
								$tier_info = $this->Tier->get_info_by_name($tier_data['name']);
						
								//Couldn't find tier
								if (!$tier_info->id)
								{
									continue;
								}
						
								$price_or_percent = $tier_data['value'];
								$tier_type = $tier_data['type'];
								$tier_id = $tier_info->id;
						
								if ($price_or_percent)
								{				
									$tier_data=array('tier_id'=>$tier_id);
									$tier_data['item_id'] = $item_id;

									if ($tier_type == 'unit_price')
									{
										$tier_data['unit_price'] = $price_or_percent;
										$tier_data['percent_off'] = NULL;
										$tier_data['cost_plus_percent'] = NULL;
										$tier_data['cost_plus_fixed_amount'] = NULL;
									}
									elseif($tier_type == 'percent_off')
									{
										$tier_data['percent_off'] = (float)$price_or_percent;
										$tier_data['unit_price'] = NULL;
										$tier_data['cost_plus_percent'] = NULL;
										$tier_data['cost_plus_fixed_amount'] = NULL;
									}
									elseif($tier_type == 'cost_plus_percent')
									{
										$tier_data['percent_off'] = NULL;
										$tier_data['unit_price'] = NULL;
										$tier_data['cost_plus_percent'] = (float)$price_or_percent;
										$tier_data['cost_plus_fixed_amount'] = NULL;
									}
									elseif($tier_type == 'cost_plus_fixed_amount')
									{
										$tier_data['percent_off'] = NULL;
										$tier_data['unit_price'] = NULL;
										$tier_data['cost_plus_percent'] = NULL;
										$tier_data['cost_plus_fixed_amount'] = (float)$price_or_percent;
									}
					
									$this->Item->save_item_tiers($tier_data,$item_id);
								}
								else
								{
									$this->Item->delete_tier_price($tier_id, $item_id);
								}
							}
						}
						else
						{
							$this->Item->delete_all_tier_prices($item_id);
						}
					}
					elseif($key=='locations')
					{
						$this->_save_item_location_data($value,$item_id);
					}
    			elseif($key!='custom_fields' && $key!='images')
    			{
						$item_data[$key] = $value;
    			}
    	}
    	
			$this->_populate_custom_fields($item_request,$item_data);
			$this->_save_and_populate_images($item_request,$item_id);
    	$return = $this->Item->save($item_data,$item_id);

    	
			return $return;
    }
    
    public function _update($item_id)
    {
			if (isset($_FILES["images"]["tmp_name"][0]))
			{
				$item_request = json_decode($_POST['item'],TRUE);
			}
			else
			{
				$item_request = json_decode(file_get_contents('php://input'),TRUE);
			}
			
      if ($this->_update_item($item_id, $item_request))
			{
				$item_return = $this->_item_result_to_array($this->Item->get_info($item_id));
				$this->response($item_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
    
        
    public function batch_post()
    {
       	$this->load->model('Item');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $item_request)
    			{
    				if ($item_id = $this->_create_item($item_request))
						{
							$item_return = $this->_item_result_to_array($this->Item->get_info($item_id));
						}
						else
						{
							$item_return = array('error' => TRUE);
						}
						$response['create'][] = $item_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $item_request)
    				{
    				  $item_id = $item_request['item_id'];
							if ($this->_update_item($item_id,$item_request))
							{
								$item_return = $this->_item_result_to_array($this->Item->get_info($item_id));
							}
							else
							{
								$item_return = array('error' => TRUE);
							}
							$response['update'][] = $item_return;
    				}

    		}

    		if (!empty($delete))
    		{
    			$response['delete'] = array();
    			
    			foreach($delete as $item_id)
    			{
							if ($item_id === NULL || !is_numeric($item_id))
     				  {
								$response['delete'][] = array('error' => TRUE);
			      		break;
			      	}
			      	
			  			$item = $this->Item->get_info($item_id);
							if ($item->item_id && !$item->deleted)
							{	
									$this->Item->delete($item_id);
									$item_return = $this->_item_result_to_array($item);
									$response['delete'][] = $item_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
    
    private function _save_and_populate_images($item_request,$item_id)
    {
    	$this->load->model('Item');
    	$this->load->model('Appfile');
    	$this->load->library('image_lib');
    	if(isset($_FILES["images"]["tmp_name"][0]))
			{		
				$this->Item->delete_all_images($item_id);
				for($k=0;$k<count($_FILES["images"]['tmp_name']);$k++)
				{
					@$image_contents = file_get_contents($_FILES["images"]["tmp_name"][$k]);
					$tmpFilename = tempnam(ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir(), 'api');
					file_put_contents($tmpFilename,$image_contents);


					$config['image_library'] = 'gd2';
					$config['source_image']	= $tmpFilename;
					$config['create_thumb'] = FALSE;
					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 1200;
					$config['height']	= 900;
					$this->image_lib->initialize($config);
					$this->image_lib->resize();
					$image_contents = file_get_contents($tmpFilename);
					
					$image_file_id = $this->Appfile->save(basename($_FILES["images"]["name"][$k]), $image_contents);
					$title = isset($_POST['titles'][$k]) ? $_POST['titles'][$k] : '';
					$alt_text = isset($_POST['alt_texts'][$k]) ? $_POST['alt_texts'][$k] : '';
					$variation = isset($_POST['variation_ids'][$k]) ? $_POST['variation_ids'][$k] : NULL;
					
					$this->Item->add_image($item_id, $image_file_id);
					$this->Item->save_image_metadata($image_file_id, $title, $alt_text, $variation);
				}
				
			}
    	elseif (isset($item_request['images']) && is_array($item_request['images']))
    	{
    		$this->Item->delete_all_images($item_id);
    	  foreach($item_request['images'] as $item_image)
    	  {
					$this->load->model('Appfile');					
					@$image_contents = file_get_contents_curl($item_image['image_url']);
					$tmpFilename = tempnam(ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir(), 'api');
					file_put_contents($tmpFilename,$image_contents);


					$config['image_library'] = 'gd2';
					$config['source_image']	= $tmpFilename;
					$config['create_thumb'] = FALSE;
					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 1200;
					$config['height']	= 900;
					$this->image_lib->initialize($config);
					$this->image_lib->resize();
					$this->load->model('Appfile');
					$image_contents = file_get_contents($tmpFilename);
					if ($image_contents)
					{
						$image_file_id = $this->Appfile->save(basename($item_image['image_url']), $image_contents);
						$this->Item->add_image($item_id, $image_file_id);
						
						$title = isset($item_image['title']) ? $item_image['title'] : '';
						$alt_text = isset($item_image['alt_text']) ? $item_image['alt_text'] : '';
						$variation = isset($item_image['variation_id']) &&  $item_image['variation_id'] ? $item_image['variation_id'] : NULL;
						$this->Item->save_image_metadata($image_file_id, $title, $alt_text, $variation);
						
						$main_image =  isset($item_image['main_image'])  && $item_image['main_image'] ? TRUE : FALSE;
						
						if ($main_image)
						{
							$item_image_data = array('main_image_id' => $image_file_id);
							$this->Item->save($item_image_data,$item_id);
						}
					}
    	  }
    	  
			}
    }

}