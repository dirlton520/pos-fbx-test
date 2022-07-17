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
class Appointment_Types extends REST_Controller {
	
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
				$this->load->model('Appointment');
    }
			
		private function _appointment_types_result_to_array($appointment_types)
		{
				$appointment_types_return = array(
					'id' => (int)$appointment_types['id'],
					'name' => $appointment_types['name'],
					
				);
				return $appointment_types_return;
		}
		
		function index_delete($appointment_types_id)
		{
  		$appointment_types = $this->Appointment->get_info_category($appointment_types_id);
  					
  		if ($appointment_types->id && !$appointment_types->deleted)
  		{
				$this->Appointment->delete_category($appointment_types->id);
		    $appointment_types_return = $this->_appointment_types_result_to_array((array)$appointment_types);
				
				$this->response($appointment_types_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($appointment_types_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($appointment_types_id === NULL)
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
					
					$appointment_types = $this->Appointment->search_category($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir);
					$total_records = $this->Appointment->search_category_count_all($search, 0,10000);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$appointment_types = $this->Appointment->get_all_categories($limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir);
					$total_records = $this->Appointment->count_all_categories();
				}
				
				$appointment_types_return = array();
				foreach($appointment_types as $id=>$appointment_types)
				{
						$appointment_types_return[] = $this->_appointment_types_result_to_array(array('id' => $id,'name' => $appointment_types['name']));
				}
				
				header("x-total-records: $total_records");
				
				$this->response($appointment_types_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$appointment_types = $this->Appointment->get_info_category($appointment_types_id);
      		if ($appointment_types->id)
      		{
      			$appointment_types_return = $this->_appointment_types_result_to_array((array)$appointment_types);
						$this->response($appointment_types_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($appointment_types_id = NULL)
    {
			$appointment_types_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($appointment_types_id!== NULL)
			{
				$appointment_types_id = $this->_update_appointment_types($appointment_types_id,$appointment_types_request);
				$appointment_type_return = $this->_appointment_types_result_to_array((array)$this->Appointment->get_info_category($appointment_types_id));
				$this->response($appointment_type_return, REST_Controller::HTTP_OK);
			}
			
			if ($appointment_type_id = $this->_create_appointment_types($appointment_types_request))
			{
				$appointment_type_return = $this->_appointment_types_result_to_array((array)$this->Appointment->get_info_category($appointment_type_id));
				$this->response($appointment_type_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Appointment');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $appointment_types_request)
    			{
    				if ($id = $this->_create_appointment_types($appointment_types_request))
						{
							$appointment_types_return = $this->_appointment_types_result_to_array((array)$this->Appointment->get_info_category($id));
						}
						else
						{
							$appointment_types_return = array('error' => TRUE);
						}
						$response['create'][] = $appointment_types_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $appointment_types_request)
    				{
							if ($this->_update_appointment_types($appointment_types_request['id'],$appointment_types_request))
							{
								$appointment_types_return = $this->_appointment_types_result_to_array((array)$this->Appointment->get_info_category($appointment_types_request['id']));
							}
							else
							{
								$appointment_types_return = array('error' => TRUE);
							}
							$response['update'][] = $appointment_types_return;
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
			      	
			  			$appointment_types = $this->Appointment->get_info_category($id);
										
							if ($appointment_types->id && !$appointment_types->deleted)
							{	
									$this->Appointment->delete_category($appointment_types->id);
									$appointment_types_return = $this->_appointment_types_result_to_array((array)$appointment_types);
									$response['delete'][] = $appointment_types_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_appointment_types($appointment_types_request)
    {
			return $this->Appointment->save_category($appointment_types_request['name']);
    }
    
    private function _update_appointment_types($appointment_types_id,$appointment_types_request)
    {			
			return $this->Appointment->save_category($appointment_types_request['name'],$appointment_types_id);
    }
		
}