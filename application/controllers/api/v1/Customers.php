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
class Customers extends REST_Controller {
	
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
	
		private function _amount_to_spend_for_next_point($current_spend_for_points)
		{
			if($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
			{
				list($spend_amount_for_points, ) = explode(":",$this->config->item('spend_to_point_ratio'),2);
				$spend_amount_for_points = (float)$spend_amount_for_points;
				
				return ($spend_amount_for_points - (float)$current_spend_for_points);
			}	
		}

		private function _remaining_sales_before_discount($current_sales_for_discount)
		{
			if($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'simple')
			{
				$number_of_sales_for_discount = (int)$this->config->item('number_of_sales_for_discount');
				return ($number_of_sales_for_discount - (int)$current_sales_for_discount);
			}
		}
		
		private function _customer_result_to_array($customer)
		{
				$customer_return = array(
					'person_id' => (int)$customer->person_id,
					'first_name' => $customer->first_name,
					'last_name' => $customer->last_name,
					'email' => $customer->email,
					'phone_number' => $customer->phone_number,
					'address_1' => $customer->address_1,
					'address_2' => $customer->address_2,
					'city' => $customer->city,
					'state' => $customer->state,
					'zip' => $customer->zip,
					'country' => $customer->country,
					'comments' => $customer->comments,
					'internal_notes' => $customer->internal_notes,
					'custom_fields' => array(),
					'company_name' => $customer->company_name,
					'tier_id' => (int)$customer->tier_id,
					'account_number' => $customer->account_number,
					'taxable' => (boolean)$customer->taxable,
					'tax_certificate' => $customer->tax_certificate,
					'override_default_tax' => (boolean)$customer->override_default_tax,
					'tax_class_id' => (int)$customer->tax_class_id,
					'balance' => (float)$customer->balance,
					'credit_limit' => (float)$customer->credit_limit,
					'disable_loyalty' => (boolean)$customer->disable_loyalty,
					'points' => (int)$customer->points,
					'amount_to_spend_for_next_point' => (float)$this->_amount_to_spend_for_next_point($customer->current_spend_for_points),
					'remaining_sales_before_discount' => (float)$this->_remaining_sales_before_discount($customer->current_sales_for_discount),
					'image_url' => $customer->image_id ? secure_app_file_url($customer->image_id) : '',
					'created_at' => $customer->create_date ? date(get_date_format().' '.get_time_format(), strtotime($customer->create_date)) : NULL,
					'location_id' => $customer->location_id ? (int)$customer->location_id : NULL,
					'customer_info_popup' => $customer->customer_info_popup ? $customer->customer_info_popup : NULL,
					'auto_email_receipt' => $customer->auto_email_receipt ? $customer->auto_email_receipt : 0,
				);

				for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
				{
					if($this->Customer->get_custom_field($k) !== false)
					{
						$field = array();
						$field['label']= $this->Customer->get_custom_field($k);
						if($this->Customer->get_custom_field($k,'type') == 'date')
						{
							$field['value'] = date_as_display_date($customer->{"custom_field_{$k}_value"});
						}
						else
						{
							$field['value'] = $customer->{"custom_field_{$k}_value"};
						}
						
						$customer_return['custom_fields'][$field['label']] = $field['value'];

					}
	
				}
				
				return $customer_return;
		}

		public function index_delete($person_id)
		{
			$this->load->model('Customer');

			if ($person_id === NULL || !is_numeric($person_id))
      {
      		$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
			}
			  $customer = $this->Customer->get_info($person_id);
      	if ($customer->person_id && !$customer->deleted)
				{	
						$this->Customer->delete($person_id);
				    $customer_return = $this->_customer_result_to_array($customer);
						$this->response($customer_return, REST_Controller::HTTP_OK);
				}
				else
				{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
				}
			
		}
				
    public function index_get($person_id = NULL)
    {
			$this->load->model('Customer');
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($person_id === NULL)
      {
      	$search = $this->input->get('search');
      	$location_id = $this->input->get('location_id');
      	$search_field = $this->input->get('search_field');
				$offset = $this->input->get('offset');
				$limit = $this->input->get('limit');
				
				if ($limit !== NULL && $limit > 100)
				{
					$limit = 100;
				}

				
				if ($search)
				{
					if ($search_field !== NULL)
					{
						$custom_fields_map = array();
			
						for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
						{
							if($this->Customer->get_custom_field($k) !== false)
							{
								$custom_fields_map[$this->Customer->get_custom_field($k)] = "custom_field_${k}_value";
							}
						}
						
						if (isset($custom_fields_map[$search_field]))
						{
							$search_field = $custom_fields_map[$search_field];
						}
						
					}
					
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'last_name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$customers = $this->Customer->search($search,$location_id, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$search_field)->result();
					$total_records = $this->Customer->search_count_all($search,$location_id, 0,10000,$search_field);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'pid';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$customers = $this->Customer->get_all($location_id,0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result();
					$total_records = $this->Customer->count_all($location_id,0);
				}
				
				$customers_return = array();
				foreach($customers as $customer)
				{
						$customers_return[] = $this->_customer_result_to_array($customer);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($customers_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
      			if (!is_numeric($person_id))
      			{
							$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
      			}
      			
        		$customer = $this->Customer->get_info($person_id);
        		
        		if ($customer->person_id)
        		{
        			$customer_return = $this->_customer_result_to_array($customer);
							$this->response($customer_return, REST_Controller::HTTP_OK);
					}
					else
					{
							$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
    
    public function index_post($customer_id = NULL)
    {
			if ($customer_id!== NULL)
			{
				$this->_update($customer_id);
				return;
			}
			
    	$this->load->model('Customer');
			if (isset($_FILES['image']))
			{
				$customer_request = json_decode($_POST['customer'],TRUE);
			}
			else
			{
				$customer_request = json_decode(file_get_contents('php://input'),TRUE);
			}
			
			if ($customer_person_id = $this->_create_customer($customer_request))
			{
				$customer_return = $this->_customer_result_to_array($this->Customer->get_info($customer_person_id));
				$this->response($customer_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
    
    private function _save_and_populate_image_id($customer_request,&$person_data)
    {
    	if (isset($customer_request['image_url']) && $customer_request['image_url'])
    	{
				$this->load->model('Appfile');
				@$image_contents = file_get_contents_curl($customer_request['image_url']);
		
				if ($image_contents)
				{
					$image_file_id = $this->Appfile->save(basename($customer_request['image_url']), $image_contents);
					
					if ($image_file_id)
					{
						$person_data['image_id'] = $image_file_id;
					}
				}
			}
			elseif(isset($_FILES["image"]["tmp_name"]))
			{					
					$this->load->model('Appfile');
					$image_file_id = $this->Appfile->save(basename($_FILES["image"]["name"]), file_get_contents($_FILES["image"]["tmp_name"]));
					if ($image_file_id)
					{
						$person_data['image_id'] = $image_file_id;
					}
			}
    }
    private function _populate_custom_fields($customer_request,&$customer_data)
    {
    	$custom_fields_map = array();
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				if($this->Customer->get_custom_field($k) !== false)
				{
					$custom_fields_map[$this->Customer->get_custom_field($k)] = array('index' => $k, 'type' => $this->Customer->get_custom_field($k,'type'));
				}

			}
			if (isset($customer_request['custom_fields']))
			{
				foreach($customer_request['custom_fields'] as $custom_field => $custom_field_value)
				{
					if(isset($custom_fields_map[$custom_field]))
					{
						$key = $custom_fields_map[$custom_field]['index'];
						$type = $custom_fields_map[$custom_field]['type'];
					
						if ($type == 'date')
						{
							$customer_data["custom_field_{$key}_value"] = strtotime($custom_field_value);
						}
						else
						{
							$customer_data["custom_field_{$key}_value"] = $custom_field_value;
						}
					}
				}
			}
    }
    
    private function _create_customer($customer_request)
    {
    	 $this->load->model('Customer');

			$person_data = array(
			'first_name'=>isset($customer_request['first_name']) ? $customer_request['first_name'] : '',
			'last_name'=>isset($customer_request['last_name']) ? $customer_request['last_name'] : '',
			'email'=>isset($customer_request['email']) ? $customer_request['email'] : '',
			'phone_number'=>isset($customer_request['phone_number']) ? $customer_request['phone_number'] : '',
			'address_1'=>isset($customer_request['address_1']) ? $customer_request['address_1'] : '',
			'address_2'=>isset($customer_request['address_2']) ? $customer_request['address_2'] : '',
			'city'=>isset($customer_request['city']) ? $customer_request['city'] : '',
			'state'=>isset($customer_request['state']) ? $customer_request['state'] : '',
			'zip'=>isset($customer_request['zip']) ? $customer_request['zip'] : '',
			'country'=>isset($customer_request['country']) ? $customer_request['country'] : '',
			'comments'=>isset($customer_request['comments']) ? $customer_request['comments'] : '',
			);
		
			$customer_data=array(
				'company_name'=>isset($customer_request['company_name']) ? $customer_request['company_name'] : '',
				'tier_id'=>isset($customer_request['tier_id']) &&  $customer_request['tier_id'] ? $customer_request['tier_id'] : NULL,
				'account_number'=>isset($customer_request['account_number']) ? $customer_request['account_number'] : NULL,
				'taxable'=>(!isset($customer_request['taxable']) || (isset($customer_request['taxable']) && $customer_request['taxable'])) ? 1 : 0,
				'tax_certificate'=>isset($customer_request['tax_certificate']) ? $customer_request['tax_certificate'] : '',
				'override_default_tax'=>isset($customer_request['override_default_tax']) && $customer_request['override_default_tax'] ? 1 : 0,
				'tax_class_id'=>isset($customer_request['tax_class_id']) ? $customer_request['tax_class_id'] : NULL,
				'balance'=>isset($customer_request['balance']) ? $customer_request['balance'] : 0,
				'credit_limit'=>isset($customer_request['credit_limit']) ? $customer_request['credit_limit'] : NULL,
				'points'=>isset($customer_request['points']) ? $customer_request['points'] : 0,
				'internal_notes'=>isset($customer_request['internal_notes']) ? $customer_request['internal_notes'] : '',
				'disable_loyalty'=>isset($customer_request['disable_loyalty']) && $customer_request['disable_loyalty'] ? 1 : 0,
				'location_id'=>isset($customer_request['location_id']) && $customer_request['location_id'] ? $customer_request['location_id'] : NULL,
				'customer_info_popup' => isset($customer_request['customer_info_popup']) && $customer_request['customer_info_popup'] ? $customer_request['customer_info_popup'] : NULL,
				'auto_email_receipt' => isset($customer_request['auto_email_receipt']) && $customer_request['auto_email_receipt'] ? 1 : 0,
				'auto_email_receipt' => isset($customer_request['always_sms_receipt']) && $customer_request['always_sms_receipt'] ? 1 : 0,
			);
			
			$this->_populate_custom_fields($customer_request,$customer_data);
			$this->_save_and_populate_image_id($customer_request,$person_data);
			$this->Customer->save_customer($person_data,$customer_data, FALSE,isset($customer_request) && $customer_request['skip_webhook'] ? TRUE : FALSE);
			return $customer_data['person_id'];
    }
    
    private function _update_customer($customer_person_id,$customer_request)
    {
   	  $this->load->model('Customer');

			$person_data = array();
			$customer_data = array();
			
    	$person_keys = array('first_name','last_name','email','phone_number','address_1','address_2','city','state','zip','country','comments');
    	$customer_keys = array('company_name','tier_id','account_number','taxable','tax_certificate','override_default_tax','tax_class_id','balance','credit_limit','points','disable_loyalty','location_id','internal_notes','customer_info_popup','auto_email_receipt','always_sms_receipt');
    	
    	foreach($customer_request as $key=>$value)
    	{
				if(in_array($key,$person_keys))
				{
					$person_data[$key] = $value;
				}
				elseif(in_array($key,$customer_keys))
				{
					$customer_data[$key] = $value;
				}
    	}
    	
			$this->_populate_custom_fields($customer_request,$customer_data);
			$this->_save_and_populate_image_id($customer_request,$person_data);
    	return $this->Customer->save_customer($person_data,$customer_data,$customer_person_id,isset($customer_request) && $customer_request['skip_webhook'] ? TRUE : FALSE);
    }
    
    public function _update($customer_person_id)
    {
   		if (isset($_FILES['image']))
			{
				$customer_request = json_decode($_POST['customer'],TRUE);
			}
			else
			{
				$customer_request = json_decode(file_get_contents('php://input'),TRUE);
			}
			
      if ($this->_update_customer($customer_person_id, $customer_request))
			{
				$customer_return = $this->_customer_result_to_array($this->Customer->get_info($customer_person_id));
				$this->response($customer_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
    
        
    public function batch_post()
    {
       	$this->load->model('Customer');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $customer_request)
    			{
    				if ($customer_person_id = $this->_create_customer($customer_request))
						{
							$customer_return = $this->_customer_result_to_array($this->Customer->get_info($customer_person_id));
						}
						else
						{
							$customer_return = array('error' => TRUE);
						}
						$response['create'][] = $customer_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $customer_request)
    				{
							if ($this->_update_customer($customer_request['person_id'],$customer_request))
							{
								$customer_return = $this->_customer_result_to_array($this->Customer->get_info($customer_request['person_id']));
							}
							else
							{
								$customer_return = array('error' => TRUE);
							}
							$response['update'][] = $customer_return;
    				}

    		}

    		if (!empty($delete))
    		{
    			$response['delete'] = array();
    			
    			foreach($delete as $person_id)
    			{
							if ($person_id === NULL || !is_numeric($person_id))
     				  {
								$response['delete'][] = array('error' => TRUE);
			      		break;
			      	}
			      	
			  			$customer = $this->Customer->get_info($person_id);
							if ($customer->person_id && !$customer->deleted)
							{	
									$this->Customer->delete($person_id);
									$customer_return = $this->_customer_result_to_array($customer);
									$response['delete'][] = $customer_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
}
