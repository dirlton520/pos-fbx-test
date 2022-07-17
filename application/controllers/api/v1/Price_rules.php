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
class Price_rules extends REST_Controller {
	
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
				$this->load->model('Price_rule');
    }
			
		private function _price_rules_result_to_array($price_rules)
		{
			$this->load->helper('date');
			
			$item_ids = array();
			foreach($this->Price_rule->get_rule_items($price_rules['id']) as $item)
			{
				$item_ids[] = $item['item_id'];
			}
			
			$item_kit_ids = array();
			foreach($this->Price_rule->get_rule_item_kits($price_rules['id']) as $item_kit)
			{
				$item_kit_ids[] = $item_kit['item_kit_id'];
			}
			
			$tags = array();
			foreach($this->Price_rule->get_rule_tags($price_rules['id']) as $tag)
			{
				$tags[] = $tag['name'];
			}
			
			$category_ids = array();
			foreach($this->Price_rule->get_rule_categories($price_rules['id']) as $category)
			{
				$category_ids[] = $category['id'];
			}
			
			$manufacturer_ids = array();
			foreach($this->Price_rule->get_rule_manus($price_rules['id']) as $manufacturer)
			{
				$manufacturer_ids[] = $manufacturer['id'];
			}
			
			$price_breaks = array();
			foreach($this->Price_rule->get_price_breaks($price_rules['id']) as $price_break)
			{
				$price_breaks[] = array('item_qty_to_buy' => to_quantity($price_break['item_qty_to_buy'], false),'discount_per_unit_fixed'  => $price_break['discount_per_unit_fixed'] ? to_currency_no_money($price_break['discount_per_unit_fixed']) : NULL,'discount_per_unit_percent' => to_quantity($price_break['discount_per_unit_percent'], false));
			}
			
			$location_ids = array();
			
			foreach($this->Price_rule->get_price_rule_locations($price_rules['id']) as $price_break_location)
			{
				$location_ids[] = $price_break_location['location_id'];
			}
			
			
				$price_rules_return = array(
					'id' => (int)$price_rules['id'],
					'name' => $price_rules['name'],
					'type' => $price_rules['type'],
					'start_date' => $price_rules['start_date'] ? date_as_display_datetime($price_rules['start_date']) : NULL,
					'end_date' => $price_rules['end_date'] ? date_as_display_datetime($price_rules['end_date']) : NULL,
					'active' => (boolean)$price_rules['active'],
					'items_to_buy' => to_quantity($price_rules['items_to_buy'], false),
					'items_to_get' => to_quantity($price_rules['items_to_get'], false),
					'percent_off' => to_quantity($price_rules['percent_off'], false),
					'fixed_off' => $price_rules['fixed_off'] ? to_currency_no_money($price_rules['fixed_off']) : NULL,
					'spend_amount' => $price_rules['spend_amount'] ? to_currency_no_money($price_rules['spend_amount']) : NULL,
					'num_times_to_apply' => to_quantity($price_rules['num_times_to_apply'], false),
					'coupon_code' => $price_rules['coupon_code'],
					'description' => $price_rules['description'],
					'show_on_receipt' => (boolean)$price_rules['show_on_receipt'],
					'item_ids' => $item_ids,
					'item_kit_ids' => $item_kit_ids,
					'tags' => $tags,
					'category_ids' => $category_ids,
					'manufacturer_ids' => $manufacturer_ids,
					'price_breaks' => $price_breaks,
					'coupon_spend_amount' => to_currency_no_money($price_rules['coupon_spend_amount']),
					'location_ids' => $location_ids,
				);
				return $price_rules_return;
		}
		
		function index_delete($price_rules_id)
		{
  		$price_rules = $this->Price_rule->get_rule_info($price_rules_id);
  					
  		if ($price_rules['id'] && !$price_rules['deleted'])
  		{
				$this->Price_rule->delete(array($price_rules['id']));
		    $price_rules_return = $this->_price_rules_result_to_array($price_rules);
				
				$this->response($price_rules_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($price_rules_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($price_rules_id === NULL)
      {
      	$search = $this->input->get('search');
				$offset = $this->input->get('offset');
				$limit = $this->input->get('limit');
				
				if ($limit !== NULL && $limit > 100)
				{
					$limit = 100;
				}

				if ($search)
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$price_rules = $this->Price_rule->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result();
					$total_records = $this->Price_rule->search_count_all($search, 0,10000);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$price_rules = $this->Price_rule->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result();
					$total_records = $this->Price_rule->count_all(0);
				}
				
				$price_rules_return = array();
				foreach($price_rules as $price_rules)
				{
						$price_rules_return[] = $this->_price_rules_result_to_array((array)$price_rules);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($price_rules_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$price_rules = $this->Price_rule->get_rule_info($price_rules_id);
      							
      		if ($price_rules['id'])
      		{
      			$price_rules_return = $this->_price_rules_result_to_array($price_rules);
						$this->response($price_rules_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($price_rules_id = NULL)
    {
			$price_rules_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($price_rules_id!== NULL)
			{
				$price_rules_id = $this->_create_or_update_price_rules($price_rules_id,$price_rules_request);
				$price_rule_return = $this->_price_rules_result_to_array($this->Price_rule->get_rule_info($price_rules_id));
				$this->response($price_rule_return, REST_Controller::HTTP_OK);
			}
			
			if ($price_rule_id = $this->_create_or_update_price_rules(-1,$price_rules_request))
			{
				$price_rule_return = $this->_price_rules_result_to_array($this->Price_rule->get_rule_info($price_rule_id));
				$this->response($price_rule_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Price_rule');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $price_rules_request)
    			{
    				if ($id = $this->_create_or_update_price_rules(-1,$price_rules_request))
						{
							$price_rules_return = $this->_price_rules_result_to_array($this->Price_rule->get_rule_info($id));
						}
						else
						{
							$price_rules_return = array('error' => TRUE);
						}
						$response['create'][] = $price_rules_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $price_rules_request)
    				{
							if ($this->_create_or_update_price_rules($price_rules_request['id'],$price_rules_request))
							{
								$price_rules_return = $this->_price_rules_result_to_array($this->Price_rule->get_rule_info($price_rules_request['id']));
							}
							else
							{
								$price_rules_return = array('error' => TRUE);
							}
							$response['update'][] = $price_rules_return;
    				}

    		}

    		if (!empty($delete))
    		{
    			$response['delete'] = array();
    			
    			foreach($delete as $id)
    			{
							if ($id === NULL)
     				  {
								$response['delete'][] = array('error' => TRUE);
			      		break;
			      	}
			      	
			  			$price_rules = $this->Price_rule->get_rule_info($id);
										
							if ($price_rules['id'] && !$price_rules['deleted'])
							{	
									$this->Price_rule->delete(array($price_rules['id']));
									$price_rules_return = $this->_price_rules_result_to_array($price_rules);
									$response['delete'][] = $price_rules_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_or_update_price_rules($price_rule_id, $price_rules_request)
    {
    	$this->load->model('Price_rule');
			$rule_data = array();
			$item_ids = array();
			$item_kit_ids = array();
			$category_ids = array();
			$tag_ids = array();
			$price_breaks = array();
			$locations_ids = array();
			
			if(isset($price_rules_request['name']))
			{
				$rule_data['name'] = $price_rules_request['name'];
			}
			
			if(isset($price_rules_request['type']))
			{
				$rule_data['type'] = $price_rules_request['type'];
			}
			
			if(isset($price_rules_request['start_date']))
			{
				$rule_data['start_date'] = date('Y-m-d H:i:s', strtotime($price_rules_request['start_date']));
			}

			if(isset($price_rules_request['end_date']))
			{
				$rule_data['end_date'] = date('Y-m-d H:i:s', strtotime($price_rules_request['end_date']));
			}
			
			if(isset($price_rules_request['active']))
			{
				$rule_data['active'] = $price_rules_request['active'] ? 1 : 0;
			}
			
			if(isset($price_rules_request['items_to_buy']))
			{
				$rule_data['items_to_buy'] = $price_rules_request['items_to_buy'];
			}
			
			if(isset($price_rules_request['items_to_get']))
			{
				$rule_data['items_to_get'] = $price_rules_request['items_to_get'];
			}
			
			if(isset($price_rules_request['percent_off']))
			{
				$rule_data['percent_off'] = $price_rules_request['percent_off'];
			}
			
			if(isset($price_rules_request['fixed_off']))
			{
				$rule_data['fixed_off'] = $price_rules_request['fixed_off'];
			}
			
			if(isset($price_rules_request['spend_amount']))
			{
				$rule_data['spend_amount'] = $price_rules_request['spend_amount'];
			}
			
			if(isset($price_rules_request['num_times_to_apply']))
			{
				$rule_data['num_times_to_apply'] = $price_rules_request['num_times_to_apply'];
			}
			
			if(isset($price_rules_request['coupon_code']))
			{
				$rule_data['coupon_code'] = $price_rules_request['coupon_code'];
			}
			
			if(isset($price_rules_request['description']))
			{
				$rule_data['description'] = $price_rules_request['description'];
			}
			
			if(isset($price_rules_request['show_on_receipt']))
			{
				$rule_data['show_on_receipt'] = $price_rules_request['show_on_receipt'] ? 1 : 0;
			}
			
			if (isset($price_rules_request['item_ids']))
			{
				$item_ids = $price_rules_request['item_ids'];
			}

			if (isset($price_rules_request['item_kit_ids']))
			{
				$item_kit_ids = $price_rules_request['item_kit_ids'];
			}
			
			if (isset($price_rules_request['category_ids']))
			{
				$category_ids = $price_rules_request['category_ids'];
			}
			
			if (isset($price_rules_request['manufacturer_ids']))
			{
				$manufacturer_ids = $price_rules_request['manufacturer_ids'];
			}
			
			
			if (isset($price_rules_request['price_breaks']))
			{
				$price_breaks = $price_rules_request['price_breaks'];
			}
			
			if (isset($price_rules_request['coupon_spend_amount']))
			{
				$rule_data['coupon_spend_amount'] = $price_rules_request['coupon_spend_amount'];
			}
			
			if (isset($price_rules_request['tags']))
			{
				$this->load->model('Tag');
				
				foreach($price_rules_request['tags'] as $tag_name)
				{
					if ($tag_id = $this->Tag->get_tag_id_by_name($tag_name))
					{
						$tag_ids[] = $tag_id;
					}
				}
			}
			$price_rules_request['tags'] = $tag_ids; 
			
			//Make sure we can't update primary key
			unset($rule_data['id']);
			
			$locations = array();
			if (isset($price_rules_request['location_ids']))
			{
				$locations = $price_rules_request['location_ids'];
			}
			
			$this->Price_rule->save_price_rule($price_rule_id,$rule_data, $item_ids, $item_kit_ids, $category_ids, $tag_ids, $manufacturer_ids, $price_breaks,$locations);	
			
			
			
			return isset($rule_data['id']) ? $rule_data['id'] : $price_rule_id;
    }
		
}