<?php
require_once (APPPATH."models/cart/PHPPOSCartRecv.php");
require_once (APPPATH."traits/receivingTrait.php");
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
class Receivings extends REST_Controller {
	
		use receivingTrait;
		protected $methods = [
        'index_get' => ['level' => 1, 'limit' => 20],
        'index_post' => ['level' => 2, 'limit' => 20],
        'index_delete' => ['level' => 2, 'limit' => 20],

      ];

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
				$this->cart = new PHPPOSCartRecv();
				$this->lang->load('receivings');
				$this->lang->load('module');
				$this->load->helper('items');
				$this->load->model('Receiving');
				$this->load->model('Supplier');
				$this->load->model('Category');
				$this->load->model('Tag');
				$this->load->model('Item');
				$this->load->model('Item_location');
				$this->load->model('Item_kit_location');
				$this->load->model('Item_kit_location_taxes');
				$this->load->model('Item_kit');
				$this->load->model('Item_kit_taxes');
				$this->load->model('Item_kit_items');
				$this->load->model('Item_location_taxes');
				$this->load->model('Item_taxes');
				$this->load->model('Item_taxes_finder');
				$this->load->model('Item_kit_taxes_finder');
				$this->load->model('Appfile');
				$this->load->model('Item_variation_location');

    }
		
		function index_get($receiving_id = NULL)
		{
			if ($receiving_id !== NULL)
			{
				if ($this->Receiving->exists($receiving_id))
				{
					$response = $this->recv_id_to_array($receiving_id);
					$this->response($response, REST_Controller::HTTP_OK);
				
				}
				else
				{
					$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
				}
			}
			else
			{
				
				$start_date_is_time = date('Y-m-d H:i:s', strtotime($this->input->get('start_date'))) != date('Y-m-d 00:00:00', strtotime($this->input->get('start_date')));
				$end_date_is_time = date('Y-m-d H:i:s', strtotime($this->input->get('end_date'))) != date('Y-m-d 00:00:00', strtotime($this->input->get('end_date')));
					
				if ($start_date_is_time)
				{
					$start_date = date('Y-m-d H:i:s',strtotime($this->input->get('start_date')));					
				}
				else
				{
					$start_date = date('Y-m-d',strtotime($this->input->get('start_date')));
				}
				
				if ($end_date_is_time)
				{
					$end_date = date('Y-m-d H:i:s',strtotime($this->input->get('end_date')));
				}
				else
				{
					$end_date = date('Y-m-d 23:59:59',strtotime($this->input->get('end_date')));
				}
				
				$receiving_ids = $this->Receiving->get_receiving_ids_for_range($start_date,$end_date);
				
				$response = array();
				
				foreach($receiving_ids as $receiving_id)
				{
					$response[] = $this->recv_id_to_array($receiving_id);
				}
				
				$total_records = count($response);
				header("x-total-records: $total_records");
				
				$this->response($response, REST_Controller::HTTP_OK);
			}
		}
		
		
		function index_post($receiving_id = NULL)
		{
			$receiving_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if (isset($receiving_request['location_id']) && $receiving_request['location_id'])
			{
				$this->cart->location_id = $receiving_request['location_id'];
			}
			else
			{
				$this->cart->location_id = 1;
			}
			date_default_timezone_set($this->Location->get_info_for_key('timezone',$this->cart->location_id));
			
			if (isset($receiving_request['transfer_location_id']) && $receiving_request['transfer_location_id'])
			{
				$this->cart->set_mode('transfer');
				$this->cart->transfer_location_id = $receiving_request['transfer_location_id'];
			}
			
			if (isset($receiving_request['register_id']) && $receiving_request['register_id'])
			{
				$this->cart->register_id = $receiving_request['register_id'];
			}
			
			if ($receiving_id)
			{
				$this->cart->receiving_id = $receiving_id;
			}
			
			if (isset($receiving_request['excluded_taxes']) && is_array($receiving_request['excluded_taxes']))
			{
				foreach($receiving_request['excluded_taxes'] as $excluded_tax)
				{
					$this->cart->add_excluded_tax($excluded_tax);
				}
			}
			

			if (isset($receiving_request['employee_id']) && $receiving_request['employee_id'])
			{
				$this->cart->employee_id = $receiving_request['employee_id'];
			}
			else
			{
				$this->cart->employee_id = 1;
			}
			
			if (isset($receiving_request['supplier_id']) && $receiving_request['supplier_id'])
			{
				$this->cart->supplier_id = $receiving_request['supplier_id'];
			}
			
			if (isset($receiving_request['comment']) && $receiving_request['comment'])
			{
				$this->cart->comment = $receiving_request['comment'];
			}
			
			if (isset($receiving_request['is_po']))
			{
				$this->cart->is_po = (boolean)$receiving_request['is_po'];
			}
			
			if (isset($receiving_request['paid_store_account_ids']))
			{
				$this->cart->paid_store_account_ids = $receiving_request['paid_store_account_ids'];
			}
			if (isset($receiving_request['suspended']))
			{
				$this->cart->suspended = $receiving_request['suspended'];
			}
			
			if (isset($receiving_request['shipping_cost']))
			{
				$this->cart->shipping_cost = $receiving_request['shipping_cost'];
			}
			

			if (isset($receiving_request['change_cart_date']) && $receiving_request['change_cart_date'])
			{
				$this->cart->change_date_enable = TRUE;
				$this->cart->change_cart_date = date('Y-m-d H:i:s',strtotime($receiving_request['change_cart_date']));
			}
			
			if (isset($receiving_request['payments']))
			{
				foreach($receiving_request['payments'] as $payment)
				{
					$this->cart->add_payment(new PHPPOSCartPaymentRecv($payment));
				}
			}
			
			if (isset($receiving_request['cart_items']))
			{
				foreach($receiving_request['cart_items'] as $item)
				{
						$item['type'] = 'receiving';
						$item['scan'] = $item['item_id'].(isset($item['variation_id']) && $item['variation_id'] ? '#'.$item['variation_id'] : '').'|FORCE_ITEM_ID|';
						unset($item['item_id']);
						$item['cart'] = $this->cart;
						
						$item_to_add = new PHPPOSCartItemRecv($item);
						$this->cart->add_item($item_to_add);
				}
			}
			
			$this->_populate_custom_fields($receiving_request,$this->cart);
			$this->cart->skip_webhook = isset($sale_request['skip_webhook']) && $sale_request['skip_webhook'] ? TRUE : FALSE;
			
			$receiving_id = $this->Receiving->save($this->cart);
			$response = $this->recv_id_to_array($receiving_id);
			$this->response($response, REST_Controller::HTTP_OK);
			
		}
		
		function index_delete($receiving_id)
		{
  		$receiving = $this->Receiving->get_info($receiving_id)->row();
  		
  		if ($receiving && $receiving->receiving_id && !$receiving->deleted)
  		{
				$this->Receiving->delete($receiving->receiving_id);
				$response = $this->recv_id_to_array($receiving->receiving_id);
				$this->response($response, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
		
    private function _populate_custom_fields($receiving_request,&$cart)
    {
    	$custom_fields_map = array();
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				if($this->Receiving->get_custom_field($k) !== false)
				{
					$custom_fields_map[$this->Receiving->get_custom_field($k)] = array('index' => $k, 'type' => $this->Receiving->get_custom_field($k,'type'));
				}

			}
			if (isset($receiving_request['custom_fields']))
			{
				foreach($receiving_request['custom_fields'] as $custom_field => $custom_field_value)
				{
					if(isset($custom_fields_map[$custom_field]))
					{
						$key = $custom_fields_map[$custom_field]['index'];
						$type = $custom_fields_map[$custom_field]['type'];
					
						if ($type == 'date')
						{
							$cart->{"custom_field_{$key}_value"} = strtotime($custom_field_value);
						}
						else
						{
							$cart->{"custom_field_{$key}_value"} = $custom_field_value;
						}
					}
				}
			}
    }
		
			
}
