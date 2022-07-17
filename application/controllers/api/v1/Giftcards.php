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
class Giftcards extends REST_Controller {
	
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
				$this->load->model('Giftcard');
				$this->lang->load('giftcards');
    }
			
		private function _giftcard_result_to_array($giftcard)
		{
				$giftcard_return = array(
					'id' => (int)$giftcard->giftcard_id,
					'giftcard_id' => (int)$giftcard->giftcard_id,
					'giftcard_number' => $giftcard->giftcard_number,
					'description' => $giftcard->description,
					'value' => to_currency_no_money($giftcard->value),
					'customer_id' => $giftcard->customer_id,
					'inactive' => (boolean)$giftcard->inactive,
				);
				return $giftcard_return;
		}
		
		function index_delete($giftcard_id)
		{
  		$giftcard = $this->Giftcard->get_info($giftcard_id);
  		
			if (!$giftcard->giftcard_id)
			{
				//Try lookup by giftcard number if we can't find by regular id
    		$giftcard = $this->Giftcard->get_info($this->Giftcard->get_giftcard_id($giftcard_id));
			}
			
  		if ($giftcard->giftcard_id && !$giftcard->deleted)
  		{
				$this->Giftcard->delete($giftcard->giftcard_id);
		    $giftcard_return = $this->_giftcard_result_to_array($giftcard);
				
				$this->response($giftcard_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($giftcard_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($giftcard_id === NULL)
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
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'giftcard_number';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$giftcards = $this->Giftcard->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result();
					$total_records = $this->Giftcard->search_count_all($search, 0,10000);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'giftcard_id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$giftcards = $this->Giftcard->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result();
					$total_records = $this->Giftcard->count_all(0);
				}
				
				$giftcards_return = array();
				foreach($giftcards as $giftcard)
				{
						$giftcards_return[] = $this->_giftcard_result_to_array($giftcard);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($giftcards_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$giftcard = $this->Giftcard->get_info($giftcard_id);
      		
					if (!$giftcard->giftcard_id)
					{
						//Try lookup by giftcard number if we can't find by regular id
        		$giftcard = $this->Giftcard->get_info($this->Giftcard->get_giftcard_id($giftcard_id));
					}
					
      		if ($giftcard->giftcard_id)
      		{
      			$giftcard_return = $this->_giftcard_result_to_array($giftcard);
						$this->response($giftcard_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($giftcard_id = NULL)
    {
			$giftcard_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($giftcard_id!== NULL)
			{
				$giftcard_id = $this->_update_giftcard($giftcard_id,$giftcard_request);
				$giftcard_return = $this->_giftcard_result_to_array($this->Giftcard->get_info($giftcard_id));
				$this->response($giftcard_return, REST_Controller::HTTP_OK);
			}
			
			if ($giftcard_id = $this->_create_giftcard($giftcard_request))
			{
				$giftcard_return = $this->_giftcard_result_to_array($this->Giftcard->get_info($giftcard_id));
				$this->response($giftcard_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Giftcard');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $giftcard_request)
    			{
    				if ($id = $this->_create_giftcard($giftcard_request))
						{
							$giftcard_return = $this->_giftcard_result_to_array($this->Giftcard->get_info($id));
						}
						else
						{
							$giftcard_return = array('error' => TRUE);
						}
						$response['create'][] = $giftcard_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $giftcard_request)
    				{
							if ($giftcard_update_id = $this->_update_giftcard($giftcard_request['giftcard_id'],$giftcard_request))
							{
								$giftcard_return = $this->_giftcard_result_to_array($this->Giftcard->get_info($giftcard_update_id));
							}
							else
							{
								$giftcard_return = array('error' => TRUE);
							}
							$response['update'][] = $giftcard_return;
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
			      	
			  			$giftcard = $this->Giftcard->get_info($id);
			
							if (!$giftcard->giftcard_id)
							{
								//Try lookup by giftcard number if we can't find by regular id
				    		$giftcard = $this->Giftcard->get_info($this->Giftcard->get_giftcard_id($id));
							}
							
							
							if ($giftcard->giftcard_id && !$giftcard->deleted)
							{	
									$this->Giftcard->delete($giftcard->giftcard_id);
									$giftcard_return = $this->_giftcard_result_to_array($giftcard);
									$response['delete'][] = $giftcard_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_giftcard($giftcard_request)
    {
    	 $this->load->model('Giftcard');

			$giftcard_data=array(
				'giftcard_number'=>isset($giftcard_request['giftcard_number']) ? $giftcard_request['giftcard_number'] : NULL,
				'description'=>isset($giftcard_request['description']) ? $giftcard_request['description'] : '',
				'value'=>isset($giftcard_request['value']) ? $giftcard_request['value'] : 0,
				'customer_id'=>isset($giftcard_request['customer_id']) ? $giftcard_request['customer_id'] : NULL,
				'inactive'=>isset($giftcard_request['inactive']) && $giftcard_request['inactive'] ? 1 : 0,
			);
			
			$this->Giftcard->save($giftcard_data);
			
			$this->Giftcard->log_modification(array("number" => $giftcard_request['giftcard_number'], "person"=>'API', "new_value" => isset($giftcard_request['value']) ? $giftcard_request['value'] : 0, 'old_value' => isset($giftcard_request['value']) ? $giftcard_request['value'] : 0, "type" => 'create'));
			
			return $giftcard_data['giftcard_id'];
    }
    
    private function _update_giftcard($giftcard_id,$giftcard_request)
    {
  		$giftcard = $this->Giftcard->get_info($giftcard_id);
			
			if (!$giftcard->giftcard_id)
			{
				//Try lookup by giftcard number if we can't find by regular id
    		$giftcard = $this->Giftcard->get_info($this->Giftcard->get_giftcard_id($giftcard_id));
				$giftcard_id = $giftcard->giftcard_id;
			}
			
			$old_giftcard_value = $giftcard->value;
			
			
			//Don't allow giftcard primary key to change
			if (isset($giftcard_request['giftcard_id']))
			{
				unset($giftcard_request['giftcard_id']);
			}
			
			if ($this->Giftcard->save($giftcard_request,$giftcard_id))
			{
				if (isset($giftcard_request['value']))
				{
					
					if($giftcard_request['value'] > $old_giftcard_value)
					{
						$this->Giftcard->log_modification(array("number" => $giftcard->giftcard_number, "person"=>'API', "new_value" => $giftcard_request['value'], 'old_value' => $old_giftcard_value, "type" => "update", "keyword" => lang('giftcards_added')));
					}
					else if($giftcard_request['value'] < $old_giftcard_value)
					{	
						$this->Giftcard->log_modification(array("number" => $giftcard->giftcard_number, "person"=>'API', "new_value" => $giftcard_request['value'], 'old_value' => $old_giftcard_value, "type" => "update", "keyword" => lang('giftcards_removed')));
					}
				}				
				return $giftcard_id;
			}
			
			return NULL;
    }
		
}