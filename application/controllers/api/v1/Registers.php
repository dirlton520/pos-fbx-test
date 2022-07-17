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
class Registers extends REST_Controller {
	
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
				$this->load->model('Register');
    }
			
		private function _registers_result_to_array($registers)
		{
			$this->load->helper('date');
				$registers_return = array(
					'register_id' => (int)$registers->register_id,
					'location_id' => (int)$registers->location_id,
					'name' => $registers->name,
					'iptran_device_id' => $registers->iptran_device_id,
					'emv_terminal_id' => $registers->emv_terminal_id,
					
				);
				return $registers_return;
		}
		
		function index_delete($registers_id)
		{
  		$registers = $this->Register->get_info($registers_id);
  					
  		if ($registers->register_id && !$registers->deleted)
  		{
				$this->Register->delete($registers->register_id);
		    $registers_return = $this->_registers_result_to_array($registers);
				
				$this->response($registers_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($registers_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($registers_id === NULL)
      {
      	$search = $this->input->get('search');
				$offset = $this->input->get('offset');
				$limit = $this->input->get('limit');
				
				if ($limit !== NULL && $limit > 100)
				{
					$limit = 100;
				}

				$location_id = $this->input->get('location_id') ? $this->input->get('location_id') : 1;
				if ($search)
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'register_id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$registers = $this->Register->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$location_id)->result();
					$total_records = $this->Register->search_count_all($search, 0,10000,$location_id);
				}
				else
				{					
					$registers = $this->Register->get_all($location_id)->result();
					$total_records = $this->Register->count_all($location_id);
				}
				
				$registers_return = array();
				foreach($registers as $registers)
				{
						$registers_return[] = $this->_registers_result_to_array($registers);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($registers_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$registers = $this->Register->get_info($registers_id);
      							
      		if ($registers->register_id)
      		{
      			$registers_return = $this->_registers_result_to_array($registers);
						$this->response($registers_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($registers_id = NULL)
    {
			$registers_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($registers_id!== NULL)
			{
				$registers_id = $this->_update_registers($registers_id,$registers_request);
				$register_return = $this->_registers_result_to_array($this->Register->get_info($registers_id));
				$this->response($register_return, REST_Controller::HTTP_OK);
			}
			
			if ($register_id = $this->_create_registers($registers_request))
			{
				$register_return = $this->_registers_result_to_array($this->Register->get_info($register_id));
				$this->response($register_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Register');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $registers_request)
    			{
    				if ($id = $this->_create_registers($registers_request))
						{
							$registers_return = $this->_registers_result_to_array($this->Register->get_info($id));
						}
						else
						{
							$registers_return = array('error' => TRUE);
						}
						$response['create'][] = $registers_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $registers_request)
    				{
							if ($this->_update_registers($registers_request['register_id'],$registers_request))
							{
								$registers_return = $this->_registers_result_to_array($this->Register->get_info($registers_request['register_id']));
							}
							else
							{
								$registers_return = array('error' => TRUE);
							}
							$response['update'][] = $registers_return;
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
			      	
			  			$registers = $this->Register->get_info($id);
										
							if ($registers->register_id && !$registers->deleted)
							{	
									$this->Register->delete($registers->register_id);
									$registers_return = $this->_registers_result_to_array($registers);
									$response['delete'][] = $registers_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_registers($registers_request)
    {
    	 $this->load->model('Register');

			$registers_data=array(
				'location_id' => isset($registers_request['location_id']) && $registers_request['location_id'] ? $registers_request['location_id'] : 1,
				'name' => isset($registers_request['name']) && $registers_request['name'] ? $registers_request['name'] : '',
				'iptran_device_id' => isset($registers_request['iptran_device_id']) && $registers_request['iptran_device_id'] ? $registers_request['iptran_device_id'] : '',
				'emv_terminal_id' => isset($registers_request['emv_terminal_id']) && $registers_request['emv_terminal_id'] ? $registers_request['emv_terminal_id'] : '',
			);
			
			$this->Register->save($registers_data);
			return $registers_data['register_id'];
    }
    
    private function _update_registers($registers_id,$registers_request)
    {
  		$registers = $this->Register->get_info($registers_id);
						
			//Don't allow registers primary key to change
			if (isset($registers_request['register_id']))
			{
				unset($registers_request['register_id']);
			}
			
			if ($this->Register->save($registers_request,$registers_id))
			{
				return $registers_id;
			}
			
			return NULL;
    }
		
}