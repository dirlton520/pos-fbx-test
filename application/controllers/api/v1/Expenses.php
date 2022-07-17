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
class Expenses extends REST_Controller {
	
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
				$this->load->model('Expense');
    }
			
		private function _expenses_result_to_array($expenses)
		{
			$this->load->helper('date');
				$expenses_return = array(
					'id' => (int)$expenses->id,
					'location_id' => (int)$expenses->location_id,
					'category_id' => (int)$expenses->category_id,
					'expense_type' => $expenses->expense_type,
					'expense_description' => $expenses->expense_description,
					'expense_reason' => $expenses->expense_reason,
					'expense_date' => date_as_display_datetime($expenses->expense_date),
					'expense_amount' => to_currency_no_money($expenses->expense_amount),
					'expense_tax' => to_currency_no_money($expenses->expense_tax),
					'expense_note' => $expenses->expense_note,
					'employee_id' => (int)$expenses->employee_id,
					'approved_employee_id' => (int)$expenses->approved_employee_id,
					'expense_payment_type' => $expenses->expense_payment_type,
					
				);
				return $expenses_return;
		}
		
		function index_delete($expenses_id)
		{
  		$expenses = $this->Expense->get_info($expenses_id);
  					
  		if ($expenses->id && !$expenses->deleted)
  		{
				$this->Expense->delete($expenses->id);
		    $expenses_return = $this->_expenses_result_to_array($expenses);
				
				$this->response($expenses_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($expenses_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($expenses_id === NULL)
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
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$expenses = $this->Expense->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$location_id)->result();
					$total_records = $this->Expense->search_count_all($search, 0,10000,$location_id);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$expenses = $this->Expense->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$location_id)->result();
					$total_records = $this->Expense->count_all(0,$location_id);
				}
				
				$expenses_return = array();
				foreach($expenses as $expenses)
				{
						$expenses_return[] = $this->_expenses_result_to_array($expenses);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($expenses_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$expenses = $this->Expense->get_info($expenses_id);
      							
      		if ($expenses->id)
      		{
      			$expenses_return = $this->_expenses_result_to_array($expenses);
						$this->response($expenses_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($expenses_id = NULL)
    {
			$expenses_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($expenses_id!== NULL)
			{
				$expenses_id = $this->_update_expenses($expenses_id,$expenses_request);
				$expense_return = $this->_expenses_result_to_array($this->Expense->get_info($expenses_id));
				$this->response($expense_return, REST_Controller::HTTP_OK);
			}
			
			if ($expense_id = $this->_create_expenses($expenses_request))
			{
				$expense_return = $this->_expenses_result_to_array($this->Expense->get_info($expense_id));
				$this->response($expense_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Expense');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $expenses_request)
    			{
    				if ($id = $this->_create_expenses($expenses_request))
						{
							$expenses_return = $this->_expenses_result_to_array($this->Expense->get_info($id));
						}
						else
						{
							$expenses_return = array('error' => TRUE);
						}
						$response['create'][] = $expenses_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $expenses_request)
    				{
							if ($this->_update_expenses($expenses_request['id'],$expenses_request))
							{
								$expenses_return = $this->_expenses_result_to_array($this->Expense->get_info($expenses_request['id']));
							}
							else
							{
								$expenses_return = array('error' => TRUE);
							}
							$response['update'][] = $expenses_return;
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
			      	
			  			$expenses = $this->Expense->get_info($id);
										
							if ($expenses->id && !$expenses->deleted)
							{	
									$this->Expense->delete($expenses->id);
									$expenses_return = $this->_expenses_result_to_array($expenses);
									$response['delete'][] = $expenses_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_expenses($expenses_request)
    {
    	 $this->load->model('Expense');
 			date_default_timezone_set($this->Location->get_info_for_key('timezone',isset($expenses_request['location_id']) && $expenses_request['location_id'] ? $expenses_request['location_id'] : 1));

			$expenses_data=array(
				'location_id' => isset($expenses_request['location_id']) && $expenses_request['location_id'] ? $expenses_request['location_id'] : 1,
				'category_id' => isset($expenses_request['category_id']) && $expenses_request['category_id'] ? $expenses_request['category_id'] : 1,
				'expense_type' => isset($expenses_request['expense_type']) && $expenses_request['expense_type'] ? $expenses_request['expense_type'] : '',
				'expense_description' => isset($expenses_request['expense_description']) && $expenses_request['expense_description'] ? $expenses_request['expense_description'] : '',
				'expense_reason' => isset($expenses_request['expense_reason']) && $expenses_request['expense_reason'] ? $expenses_request['expense_reason'] : '',
				'expense_date' => isset($expenses_request['expense_date']) && $expenses_request['expense_date'] ? date('Y-m-d H:i:s',strtotime($expenses_request['expense_date'])) : date('Y-m-d H:i:s'),
				'expense_amount' => isset($expenses_request['expense_amount']) && $expenses_request['expense_amount'] ? $expenses_request['expense_amount'] : 0,
				'expense_tax' => isset($expenses_request['expense_tax']) && $expenses_request['expense_tax'] ? $expenses_request['expense_tax'] : 0,
				'expense_note' => isset($expenses_request['expense_note']) && $expenses_request['expense_note'] ? $expenses_request['expense_note'] : '',
				'employee_id' => isset($expenses_request['employee_id']) && $expenses_request['employee_id'] ? $expenses_request['employee_id'] : 1,
				'approved_employee_id' => isset($expenses_request['approved_employee_id']) && $expenses_request['approved_employee_id'] ? $expenses_request['approved_employee_id'] : 1,
				'expense_payment_type' => isset($expenses_request['expense_payment_type']) && $expenses_request['expense_payment_type'] ? $expenses_request['expense_payment_type'] : '',
			);
			
			$this->Expense->save($expenses_data);
			return $expenses_data['id'];
    }
    
    private function _update_expenses($expenses_id,$expenses_request)
    {
 			date_default_timezone_set($this->Location->get_info_for_key('timezone',isset($expenses_request['location_id']) && $expenses_request['location_id'] ? $expenses_request['location_id'] : 1));
			
  		$expenses = $this->Expense->get_info($expenses_id);
						
			//Don't allow expenses primary key to change
			if (isset($expenses_request['id']))
			{
				unset($expenses_request['id']);
			}
			
			if (isset($expenses_request['expense_date']))
			{
				$expenses_request['expense_date'] = date('Y-m-d H:i:s',strtotime($expenses_request['expense_date']));
			}
			if ($this->Expense->save($expenses_request,$expenses_id))
			{
				return $expenses_id;
			}
			
			return NULL;
    }
		
}