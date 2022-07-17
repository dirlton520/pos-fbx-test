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
class Suppliers extends REST_Controller {
	
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
			
		private function _supplier_result_to_array($supplier)
		{
				$supplier_return = array(
					'person_id' => (int)$supplier->person_id,
					'first_name' => $supplier->first_name,
					'last_name' => $supplier->last_name,
					'email' => $supplier->email,
					'phone_number' => $supplier->phone_number,
					'address_1' => $supplier->address_1,
					'address_2' => $supplier->address_2,
					'city' => $supplier->city,
					'state' => $supplier->state,
					'zip' => $supplier->zip,
					'country' => $supplier->country,
					'comments' => $supplier->comments,
					'custom_fields' => array(),
					'company_name' => $supplier->company_name,
					'account_number' => $supplier->account_number,
					'override_default_tax' => (boolean)$supplier->override_default_tax,
					'tax_class_id' => (int)$supplier->tax_class_id,
					'balance' => (float)$supplier->balance,
					'image_url' => $supplier->image_id ? secure_app_file_url($supplier->image_id) : '',
					'created_at' => $supplier->create_date ? date(get_date_format().' '.get_time_format(), strtotime($supplier->create_date)) : NULL,
				);

				for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
				{
					if($this->Supplier->get_custom_field($k) !== false)
					{
						$field = array();
						$field['label']= $this->Supplier->get_custom_field($k);
						if($this->Supplier->get_custom_field($k,'type') == 'date')
						{
							$field['value'] = date_as_display_date($supplier->{"custom_field_{$k}_value"});
						}
						else
						{
							$field['value'] = $supplier->{"custom_field_{$k}_value"};
						}
						
						$supplier_return['custom_fields'][$field['label']] = $field['value'];
					}
	
				}
				
				return $supplier_return;
		}

		public function index_delete($person_id)
		{
			$this->load->model('Supplier');

			if ($person_id === NULL || !is_numeric($person_id))
      {
      		$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
			}
			  $supplier = $this->Supplier->get_info($person_id);
      	if ($supplier->person_id && !$supplier->deleted)
				{	
						$this->Supplier->delete($person_id);
				    $supplier_return = $this->_supplier_result_to_array($supplier);
						$this->response($supplier_return, REST_Controller::HTTP_OK);
				}
				else
				{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
				}
			
		}
				
    public function index_get($person_id = NULL)
    {
			$this->load->model('Supplier');
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($person_id === NULL)
      {
      	$search = $this->input->get('search');
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
							if($this->Supplier->get_custom_field($k) !== false)
							{
								$custom_fields_map[$this->Supplier->get_custom_field($k)] = "custom_field_${k}_value";
							}
						}
						
						if (isset($custom_fields_map[$search_field]))
						{
							$search_field = $custom_fields_map[$search_field];
						}
						
					}

					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'last_name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';

					$suppliers = $this->Supplier->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$search_field)->result();
					$total_records = $this->Supplier->search_count_all($search, 0,10000,$search_field);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'pid';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$suppliers = $this->Supplier->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result();
					$total_records = $this->Supplier->count_all(0);
				}
				
				$suppliers_return = array();
				foreach($suppliers as $supplier)
				{
						$suppliers_return[] = $this->_supplier_result_to_array($supplier);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($suppliers_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
      			if (!is_numeric($person_id))
      			{
							$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
      			}
      			
        		$supplier = $this->Supplier->get_info($person_id);
        		
        		if ($supplier->person_id)
        		{
        			$supplier_return = $this->_supplier_result_to_array($supplier);
							$this->response($supplier_return, REST_Controller::HTTP_OK);
					}
					else
					{
							$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
    
    public function index_post($supplier_id = NULL)
    {
			if ($supplier_id!== NULL)
			{
				$this->_update($supplier_id);
				return;
			}
			
			
    	$this->load->model('Supplier');
			if (isset($_FILES['image']))
			{
				$supplier_request = json_decode($_POST['supplier'],TRUE);
			}
			else
			{
				$supplier_request = json_decode(file_get_contents('php://input'),TRUE);
			}
			
			if ($supplier_person_id = $this->_create_supplier($supplier_request))
			{
				$supplier_return = $this->_supplier_result_to_array($this->Supplier->get_info($supplier_person_id));
				$this->response($supplier_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
    
    private function _save_and_populate_image_id($supplier_request,&$person_data)
    {
    	if (isset($supplier_request['image_url']) && $supplier_request['image_url'])
    	{
				$this->load->model('Appfile');
				@$image_contents = file_get_contents_curl($supplier_request['image_url']);
		
				if ($image_contents)
				{
					$image_file_id = $this->Appfile->save(basename($supplier_request['image_url']), $image_contents);
					
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
    private function _populate_custom_fields($supplier_request,&$supplier_data)
    {
    	$custom_fields_map = array();
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				if($this->Supplier->get_custom_field($k) !== false)
				{
					$custom_fields_map[$this->Supplier->get_custom_field($k)] = array('index' => $k, 'type' => $this->Supplier->get_custom_field($k,'type'));
				}

			}
			if (isset($supplier_request['custom_fields']))
			{
				foreach($supplier_request['custom_fields'] as $custom_field => $custom_field_value)
				{
					if(isset($custom_fields_map[$custom_field]))
					{
						$key = $custom_fields_map[$custom_field]['index'];
						$type = $custom_fields_map[$custom_field]['type'];
					
						if ($type == 'date')
						{
							$supplier_data["custom_field_{$key}_value"] = strtotime($custom_field_value);
						}
						else
						{
							$supplier_data["custom_field_{$key}_value"] = $custom_field_value;
						}
					}
				}
			}
    }
    
    private function _create_supplier($supplier_request)
    {
    	 $this->load->model('Supplier');

			$person_data = array(
			'first_name'=>isset($supplier_request['first_name']) ? $supplier_request['first_name'] : '',
			'last_name'=>isset($supplier_request['last_name']) ? $supplier_request['last_name'] : '',
			'email'=>isset($supplier_request['email']) ? $supplier_request['email'] : '',
			'phone_number'=>isset($supplier_request['phone_number']) ? $supplier_request['phone_number'] : '',
			'address_1'=>isset($supplier_request['address_1']) ? $supplier_request['address_1'] : '',
			'address_2'=>isset($supplier_request['address_2']) ? $supplier_request['address_2'] : '',
			'city'=>isset($supplier_request['city']) ? $supplier_request['city'] : '',
			'state'=>isset($supplier_request['state']) ? $supplier_request['state'] : '',
			'zip'=>isset($supplier_request['zip']) ? $supplier_request['zip'] : '',
			'country'=>isset($supplier_request['country']) ? $supplier_request['country'] : '',
			'comments'=>isset($supplier_request['comments']) ? $supplier_request['comments'] : '',
			);
		
			$supplier_data=array(
				'company_name'=>isset($supplier_request['company_name']) ? $supplier_request['company_name'] : '',
				'account_number'=>isset($supplier_request['account_number']) ? $supplier_request['account_number'] : NULL,
				'override_default_tax'=>isset($supplier_request['override_default_tax']) && $supplier_request['override_default_tax'] ? 1 : 0,
				'tax_class_id'=>isset($supplier_request['tax_class_id']) ? $supplier_request['tax_class_id'] : NULL,
				'balance'=>isset($supplier_request['balance']) ? $supplier_request['balance'] : 0,
			);
			
			$this->_populate_custom_fields($supplier_request,$supplier_data);
			$this->_save_and_populate_image_id($supplier_request,$person_data);
			$this->Supplier->save_supplier($person_data,$supplier_data);
			return $supplier_data['person_id'];
    }
    
    private function _update_supplier($supplier_person_id,$supplier_request)
    {
   	  $this->load->model('Supplier');

			$person_data = array();
			$supplier_data = array();
			
    	$person_keys = array('first_name','last_name','email','phone_number','address_1','address_2','city','state','zip','country','comments');
    	$supplier_keys = array('company_name','account_number','override_default_tax','tax_class_id','balance');
    	
    	foreach($supplier_request as $key=>$value)
    	{
				if(in_array($key,$person_keys))
				{
					$person_data[$key] = $value;
				}
				elseif(in_array($key,$supplier_keys))
				{
					$supplier_data[$key] = $value;
				}
    	}
    	
			$this->_populate_custom_fields($supplier_request,$supplier_data);
			$this->_save_and_populate_image_id($supplier_request,$person_data);
    	return $this->Supplier->save_supplier($person_data,$supplier_data,$supplier_person_id);
    }
    
    public function _update($supplier_person_id)
    {
   		if (isset($_FILES['image']))
			{
				$supplier_request = json_decode($_POST['supplier'],TRUE);
			}
			else
			{
				$supplier_request = json_decode(file_get_contents('php://input'),TRUE);
			}
			
      if ($this->_update_supplier($supplier_person_id, $supplier_request))
			{
				$supplier_return = $this->_supplier_result_to_array($this->Supplier->get_info($supplier_person_id));
				$this->response($supplier_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
    
        
    public function batch_post()
    {
       	$this->load->model('Supplier');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $supplier_request)
    			{
    				if ($supplier_person_id = $this->_create_supplier($supplier_request))
						{
							$supplier_return = $this->_supplier_result_to_array($this->Supplier->get_info($supplier_person_id));
						}
						else
						{
							$supplier_return = array('error' => TRUE);
						}
						$response['create'][] = $supplier_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $supplier_request)
    				{
							if ($this->_update_supplier($supplier_request['person_id'],$supplier_request))
							{
								$supplier_return = $this->_supplier_result_to_array($this->Supplier->get_info($supplier_request['person_id']));
							}
							else
							{
								$supplier_return = array('error' => TRUE);
							}
							$response['update'][] = $supplier_return;
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
			      	
			  			$supplier = $this->Supplier->get_info($person_id);
							if ($supplier->person_id && !$supplier->deleted)
							{	
									$this->Supplier->delete($person_id);
									$supplier_return = $this->_supplier_result_to_array($supplier);
									$response['delete'][] = $supplier_return;
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
