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
class Attributes extends REST_Controller {
	
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
				$this->load->model('Item_attribute');
				$this->load->model('Item_attribute_value');
    }
			
		private function _attributes_result_to_array($attributes)
		{
			$attr_values_result = $this->Item_attribute_value->get_values_for_attribute((int)$attributes['id'])->result_array();
			
			$attr_values = array();
			foreach($attr_values_result as $row)
			{
				$attr_values[] = $row['name'];
				
			}
				$attributes_return = array(
					'id' => (int)$attributes['id'],
					'name' => $attributes['name'],
					'values' => $attr_values,
				);
				return $attributes_return;
		}
		
		function index_delete($attributes_id)
		{
  		$attributes = $this->Item_attribute->get_info((int)$attributes_id);
  					
  		if ($attributes->id && !$attributes->deleted)
  		{
				$this->Item_attribute->delete($attributes->id);
		    $attributes_return = $this->_attributes_result_to_array((array)$attributes);
				
				$this->response($attributes_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($attributes_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($attributes_id === NULL)
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
					
					$attributes = $this->Item_attribute->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result_array();
					$total_records = $this->Item_attribute->search_count_all($search, 0,10000);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$attributes = $this->Item_attribute->get_all($limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result_array();
					$total_records = $this->Item_attribute->count_all(0);
				}
				
				$attributes_return = array();
				foreach($attributes as $attribute)
				{
						$attributes_return[] = $this->_attributes_result_to_array(array('id' => $attribute['id'],'name' => $attribute['name']));
				}
				
				header("x-total-records: $total_records");
				
				$this->response($attributes_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$attributes = $this->Item_attribute->get_info((int)$attributes_id);
      		if ($attributes->id)
      		{
      			$attributes_return = $this->_attributes_result_to_array((array)$attributes);
						$this->response($attributes_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($attributes_id = NULL)
    {
			$attributes_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($attributes_id!== NULL)
			{
				$attributes_id = $this->_update_attributes($attributes_id,$attributes_request);
				$attr_return = $this->_attributes_result_to_array((array)$this->Item_attribute->get_info((int)$attributes_id));
				$this->response($attr_return, REST_Controller::HTTP_OK);
			}
			
			if ($attribute_id = $this->_create_attributes($attributes_request))
			{
				$attr_return = $this->_attributes_result_to_array((array)$this->Item_attribute->get_info((int)$attribute_id));
				$this->response($attr_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Item_attribute');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $attributes_request)
    			{
    				if ($id = $this->_create_attributes($attributes_request))
						{
							$attributes_return = $this->_attributes_result_to_array((array)$this->Item_attribute->get_info((int)$id));
						}
						else
						{
							$attributes_return = array('error' => TRUE);
						}
						$response['create'][] = $attributes_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $attributes_request)
    				{
							if ($this->_update_attributes($attributes_request['id'],$attributes_request))
							{
								$attributes_return = $this->_attributes_result_to_array((array)$this->Item_attribute->get_info((int)$attributes_request['id']));
							}
							else
							{
								$attributes_return = array('error' => TRUE);
							}
							$response['update'][] = $attributes_return;
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
			      	
			  			$attributes = $this->Item_attribute->get_info((int)$id);
										
							if ($attributes->id && !$attributes->deleted)
							{	
									$this->Item_attribute->delete($attributes->id);
									$attributes_return = $this->_attributes_result_to_array((array)$attributes);
									$response['delete'][] = $attributes_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_attributes($attributes_request)
    {
    	$this->load->model('Item_attribute');
    	$this->load->model('Item_attribute_value');
			$attr_data = array('name' => $attributes_request['name']);
			$attr_id = $this->Item_attribute->save($attr_data);
			
			if(isset($attributes_request['values']))
			{				
				foreach($attributes_request['values'] as $attr_value)
				{
					$this->Item_attribute_value->save($attr_value,$attr_id);
				}
			}
			
			return $attr_id;
		}
    
    private function _update_attributes($attributes_id,$attributes_request)
    {			
    	$this->load->model('Item_attribute');
    	$this->load->model('Item_attribute_value');
			$attr_data = array('name' => $attributes_request['name']);
			$attr_id = $this->Item_attribute->save($attr_data,$attributes_id);
			
			if(isset($attributes_request['values']))
			{				
				foreach($attributes_request['values'] as $attr_value)
				{
					$this->Item_attribute_value->save($attr_value,$attr_id);
				}
			}
			
			return $attr_id;
    }
		
}