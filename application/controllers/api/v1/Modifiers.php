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
class Modifiers extends REST_Controller {
	
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
				$this->load->model('Item_modifier');
    }
			
		private function _modifiers_result_to_array($modifiers)
		{
			$modifier_items = $this->Item_modifier->get_modifier_items((int)$modifiers['id'])->result_array();
			
			for($k=0;$k<count($modifier_items);$k++)
			{
				$modifier_items[$k]['unit_price'] = to_currency_no_money($modifier_items[$k]['unit_price']);
				$modifier_items[$k]['cost_price'] = to_currency_no_money($modifier_items[$k]['cost_price']);
				unset($modifier_items[$k]['deleted']);
				unset($modifier_items[$k]['sort_order']);
				unset($modifier_items[$k]['modifier_id']);
			}
				$modifiers_return = array(
					'id' => (int)$modifiers['id'],
					'name' => $modifiers['name'],
					'items' => $modifier_items,
				);
				return $modifiers_return;
		}
		
		function index_delete($modifiers_id)
		{
  		$modifiers = $this->Item_modifier->get_info((int)$modifiers_id);
  					
  		if ($modifiers->id && !$modifiers->deleted)
  		{
				$this->Item_modifier->delete($modifiers->id);
		    $modifiers_return = $this->_modifiers_result_to_array((array)$modifiers);
				
				$this->response($modifiers_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($modifiers_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($modifiers_id === NULL)
      {
				$modifiers = $this->Item_modifier->get_all()->result_array();
				$total_records = count($modifiers);
				
				$modifiers_return = array();
				foreach($modifiers as $modifier)
				{
						$modifiers_return[] = $this->_modifiers_result_to_array(array('id' => $modifier['id'],'name' => $modifier['name']));
				}
				
				header("x-total-records: $total_records");
				
				$this->response($modifiers_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$modifiers = $this->Item_modifier->get_info((int)$modifiers_id);
      		if ($modifiers->id)
      		{
      			$modifiers_return = $this->_modifiers_result_to_array((array)$modifiers);
						$this->response($modifiers_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($modifiers_id = NULL)
    {
			$modifiers_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($modifiers_id!== NULL)
			{
				$modifiers_id = $this->_update_modifiers($modifiers_id,$modifiers_request);
				$attr_return = $this->_modifiers_result_to_array((array)$this->Item_modifier->get_info((int)$modifiers_id));
				$this->response($attr_return, REST_Controller::HTTP_OK);
			}
			
			if ($modifier_id = $this->_create_modifiers($modifiers_request))
			{
				$attr_return = $this->_modifiers_result_to_array((array)$this->Item_modifier->get_info((int)$modifier_id));
				$this->response($attr_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Item_modifier');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $modifiers_request)
    			{
    				if ($id = $this->_create_modifiers($modifiers_request))
						{
							$modifiers_return = $this->_modifiers_result_to_array((array)$this->Item_modifier->get_info((int)$id));
						}
						else
						{
							$modifiers_return = array('error' => TRUE);
						}
						$response['create'][] = $modifiers_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $modifiers_request)
    				{
							if ($this->_update_modifiers($modifiers_request['id'],$modifiers_request))
							{
								$modifiers_return = $this->_modifiers_result_to_array((array)$this->Item_modifier->get_info((int)$modifiers_request['id']));
							}
							else
							{
								$modifiers_return = array('error' => TRUE);
							}
							$response['update'][] = $modifiers_return;
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
			      	
			  			$modifiers = $this->Item_modifier->get_info((int)$id);
										
							if ($modifiers->id && !$modifiers->deleted)
							{	
									$this->Item_modifier->delete($modifiers->id);
									$modifiers_return = $this->_modifiers_result_to_array((array)$modifiers);
									$response['delete'][] = $modifiers_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_modifiers($modifiers_request)
    {
    	$this->load->model('Item_modifier');
			$modifier_data = array('name' => $modifiers_request['name']);
			$modifier_items_data = array();
			
			$counter = -1;
			if (isset($modifiers_request['items']))
			{
				foreach($modifiers_request['items'] as $mi)
				{
					$modifier_items_data[$counter] = array('name' => $mi['name'], 'unit_price' => isset($mi['unit_price']) ? $mi['unit_price'] : 0,'cost_price' => isset($mi['cost_price']) ? $mi['cost_price'] : 0);
					$counter--;
				}
			}
			return $this->Item_modifier->save(FALSE, $modifier_data, $modifier_items_data, array());
		}
    
    private function _update_modifiers($modifiers_id,$modifiers_request)
    {			
    	$this->load->model('Item_modifier');
			$modifier_data = array('name' => $modifiers_request['name']);
			$modifier_items_data = array();
			
			$counter = -1;
			if (isset($modifiers_request['items']))
			{
				foreach($modifiers_request['items'] as $mi)
				{
					$modifier_items_data[$counter] = array('name' => $mi['name'], 'unit_price' => isset($mi['unit_price']) ? $mi['unit_price'] : 0,'cost_price' => isset($mi['cost_price']) ? $mi['cost_price'] : 0);
					$counter--;
				}
			}
			return $this->Item_modifier->save($modifiers_id, $modifier_data, $modifier_items_data,TRUE);
    }
		
}