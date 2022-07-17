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
class Manufacturers extends REST_Controller {
	
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
				$this->load->model('Manufacturer');
    }
			
		private function _manufacturers_result_to_array($manufacturers)
		{
				$manufacturers_return = array(
					'id' => (int)$manufacturers['id'],
					'name' => $manufacturers['name'],
					
				);
				return $manufacturers_return;
		}
		
		function index_delete($manufacturers_id)
		{
  		$manufacturers = $this->Manufacturer->get_info($manufacturers_id);
  					
  		if ($manufacturers->id && !$manufacturers->deleted)
  		{
				$this->Manufacturer->delete($manufacturers->id);
		    $manufacturers_return = $this->_manufacturers_result_to_array((array)$manufacturers);
				
				$this->response($manufacturers_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($manufacturers_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($manufacturers_id === NULL)
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
					
					$manufacturers = $this->Manufacturer->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir);
					$total_records = $this->Manufacturer->search_count_all($search, 0,10000);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$manufacturers = $this->Manufacturer->get_all($limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir);
					$total_records = $this->Manufacturer->count_all(0);
				}
				
				$manufacturers_return = array();
				foreach($manufacturers as $id=>$manufacturers)
				{
						$manufacturers_return[] = $this->_manufacturers_result_to_array(array('id' => $id,'name' => $manufacturers['name']));
				}
				
				header("x-total-records: $total_records");
				
				$this->response($manufacturers_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$manufacturers = $this->Manufacturer->get_info($manufacturers_id);
      		if ($manufacturers->id)
      		{
      			$manufacturers_return = $this->_manufacturers_result_to_array((array)$manufacturers);
						$this->response($manufacturers_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($manufacturers_id = NULL)
    {
			$manufacturers_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($manufacturers_id!== NULL)
			{
				$manufacturers_id = $this->_update_manufacturers($manufacturers_id,$manufacturers_request);
				$manufacturer_return = $this->_manufacturers_result_to_array((array)$this->Manufacturer->get_info($manufacturers_id));
				$this->response($manufacturer_return, REST_Controller::HTTP_OK);
			}
			
			if ($manufacturer_id = $this->_create_manufacturers($manufacturers_request))
			{
				$manufacturer_return = $this->_manufacturers_result_to_array((array)$this->Manufacturer->get_info($manufacturer_id));
				$this->response($manufacturer_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Manufacturer');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $manufacturers_request)
    			{
    				if ($id = $this->_create_manufacturers($manufacturers_request))
						{
							$manufacturers_return = $this->_manufacturers_result_to_array((array)$this->Manufacturer->get_info($id));
						}
						else
						{
							$manufacturers_return = array('error' => TRUE);
						}
						$response['create'][] = $manufacturers_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $manufacturers_request)
    				{
							if ($this->_update_manufacturers($manufacturers_request['id'],$manufacturers_request))
							{
								$manufacturers_return = $this->_manufacturers_result_to_array((array)$this->Manufacturer->get_info($manufacturers_request['id']));
							}
							else
							{
								$manufacturers_return = array('error' => TRUE);
							}
							$response['update'][] = $manufacturers_return;
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
			      	
			  			$manufacturers = $this->Manufacturer->get_info($id);
										
							if ($manufacturers->id && !$manufacturers->deleted)
							{	
									$this->Manufacturer->delete($manufacturers->id);
									$manufacturers_return = $this->_manufacturers_result_to_array((array)$manufacturers);
									$response['delete'][] = $manufacturers_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_manufacturers($manufacturers_request)
    {
    	$this->load->model('Manufacturer');
			return $this->Manufacturer->save($manufacturers_request['name']);
    }
    
    private function _update_manufacturers($manufacturers_id,$manufacturers_request)
    {			
			return $this->Manufacturer->save($manufacturers_request['name'],$manufacturers_id);
    }
		
}