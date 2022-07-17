<?php
require_once (APPPATH."models/cart/PHPPOSCartSale.php");
require_once (APPPATH."traits/saleTrait.php");
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
class Sales extends REST_Controller {
	
	use saleTrait;
	
		protected $methods = [
        'index_get' => ['level' => 1, 'limit' => 20],
        'index_post' => ['level' => 2, 'limit' => 20],
        'index_delete' => ['level' => 2, 'limit' => 20],

      ];

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
				$this->cart = new PHPPOSCartSale();
				$this->lang->load('sales');
				$this->lang->load('module');
				$this->load->helper('order');
				$this->load->helper('items');
				$this->load->helper('sale');
				$this->load->model('Sale');
				$this->load->model('Customer');
				$this->load->model('Tier');
				$this->load->model('Category');
				$this->load->model('Giftcard');
				$this->load->model('Tag');
				$this->load->model('Item');
				$this->load->model('Item_location');
				$this->load->model('Item_kit_location');
				$this->load->model('Item_kit_location_taxes');
				$this->load->model('Item_kit');
				$this->load->model('Item_kit_items');
				$this->load->model('Item_kit_taxes');
				$this->load->model('Item_location_taxes');
				$this->load->model('Item_taxes');
				$this->load->model('Item_taxes_finder');
				$this->load->model('Item_kit_taxes_finder');
				$this->load->model('Appfile');
				$this->load->model('Item_serial_number');
				$this->load->model('Price_rule');
				$this->load->model('Shipping_provider');
				$this->load->model('Shipping_method');
				$this->lang->load('deliveries');
				$this->load->model('Item_variation_location');
				$this->load->model('Item_variations');
    }
				
		function index_get($sale_id = NULL)
		{
			if ($sale_id !== NULL)
			{
				if ($this->Sale->exists($sale_id))
				{
					$response = $this->sale_id_to_array($sale_id);
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
				
				$customer_id = $this->input->get('customer_id');
				
				$sale_ids = $this->Sale->get_sale_ids_for_range($start_date,$end_date,$customer_id);
				
				$response = array();
				
				foreach($sale_ids as $sale_id)
				{
					$response[] = $this->sale_id_to_array($sale_id);
				}
				
				$total_records = count($response);
				header("x-total-records: $total_records");
				
				$this->response($response, REST_Controller::HTTP_OK);
			}
		}
		
		function index_post($sale_id = NULL)
		{
			$sale_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if (isset($sale_request['location_id']) && $sale_request['location_id'])
			{
				$this->cart->location_id = $sale_request['location_id'];
			}
			else
			{
				$this->cart->location_id = 1;
			}
			
			if (isset($sale_request['mode']) && in_array($sale_request['mode'], array('sale','return','store_account_payment','purchase_points')))
			{
				$this->cart->set_mode($sale_request['mode']);
			}
			
			date_default_timezone_set($this->Location->get_info_for_key('timezone',$this->cart->location_id));
			
			if (isset($sale_request['register_id']) && $sale_request['register_id'])
			{
				$this->cart->register_id = $sale_request['register_id'];
			}
			
			if ($sale_id)
			{
				$this->cart->sale_id = $sale_id;
			}
			
			if (isset($sale_request['excluded_taxes']) && is_array($sale_request['excluded_taxes']))
			{
				foreach($sale_request['excluded_taxes'] as $excluded_tax)
				{
					$this->cart->add_excluded_tax($excluded_tax);
				}
			}
			

			if (isset($sale_request['employee_id']) && $sale_request['employee_id'])
			{
				$this->cart->employee_id = $sale_request['employee_id'];
			}
			else
			{
				$this->cart->employee_id = 1;
			}
			
			if (isset($sale_request['customer_id']) && $sale_request['customer_id'])
			{
				$this->cart->customer_id = $sale_request['customer_id'];
			}
			
			if (isset($sale_request['comment']) && $sale_request['comment'])
			{
				$this->cart->comment = $sale_request['comment'];
			}
			
			if (isset($sale_request['show_comment_on_receipt']) && $sale_request['show_comment_on_receipt'])
			{
				$this->cart->show_comment_on_receipt = $sale_request['show_comment_on_receipt'];
			}
			
			if (isset($sale_request['selected_tier_id']) && $sale_request['selected_tier_id'])
			{
				$this->cart->selected_tier_id = $sale_request['selected_tier_id'];
			}

			if (isset($sale_request['sold_by_employee_id']) && $sale_request['sold_by_employee_id'])
			{
				$this->cart->sold_by_employee_id = $sale_request['sold_by_employee_id'];
			}

			if (isset($sale_request['discount_reason']) && $sale_request['discount_reason'])
			{
				$this->cart->discount_reason = $sale_request['discount_reason'];
			}

			if (isset($sale_request['has_delivery']))
			{
				$this->cart->has_delivery = (boolean)$sale_request['has_delivery'];
			}
			
			if (isset($sale_request['delivery']['delivery_person_info']))
			{
				$this->cart->set_delivery_person_info($sale_request['delivery']['delivery_person_info']);
			}
			if (isset($sale_request['delivery']['delivery_info']))
			{
				$this->cart->set_delivery_info($sale_request['delivery']['delivery_info']);
			}
			
			if (isset($sale_request['delivery']['delivery_tax_group_id']))
			{
				$this->cart->set_delivery_tax_group_id($sale_request['delivery']['delivery_tax_group_id']);
			}

			if (isset($sale_request['paid_store_account_ids']))
			{
				foreach($sale_request['paid_store_account_ids'] as $paid_store_account_id)
				{
					$this->cart->add_paid_store_account_payment_id($paid_store_account_id);
				}
			}
			if (isset($sale_request['suspended']))
			{
				$this->cart->suspended = $sale_request['suspended'];
			}
			
			if (isset($sale_request['change_cart_date']) && $sale_request['change_cart_date'])
			{
				$this->cart->change_date_enable = TRUE;
				$this->cart->change_cart_date = date('Y-m-d H:i:s',strtotime($sale_request['change_cart_date']));
			}
			
			if (isset($sale_request['payments']))
			{
				foreach($sale_request['payments'] as $payment)
				{
					$this->cart->add_payment(new PHPPOSCartPaymentSale($payment));
				}
			}
			
			if (isset($sale_request['cart_items']))
			{
				foreach($sale_request['cart_items'] as $item)
				{
					if (isset($item['modifier_items']) && is_array($item['modifier_items']))
					{
						$modifier_items = array();
						
						foreach($item['modifier_items'] as $mi)
						{
							$display_name = to_currency($mi['unit_price']).': '.$mi['name'];
							
							$modifier_items[$mi['id']] = array('display_name' => $display_name, 'unit_price' => $mi['unit_price'],'cost_price' => $mi['cost_price']);
						}
						
						$item['modifier_items'] = $modifier_items;
					}
					
					//Item
					if (isset($item['item_id']))
					{
						$cur_item_info = $this->Item->get_info($item['item_id']);
						$cur_item_location_info = $this->Item_location->get_info($item['item_id'],$this->cart->location_id);
						$cur_item_variation_info = $this->Item_variations->get_info(isset($item['variation_id']) ? $item['variation_id'] : -1);
						
						$item['type'] = 'sale';
						$item['cart'] = $this->cart;
						if ($cur_item_variation_info && $cur_item_variation_info->unit_price)
						{
							$item['regular_price'] = $cur_item_variation_info->unit_price;
						}
						else
						{
							$item['regular_price'] = ($cur_item_location_info && $cur_item_location_info->unit_price) ? $cur_item_location_info->unit_price : $cur_item_info->unit_price;
						}
						
						$item['scan'] = $item['item_id'].(isset($item['variation_id']) && $item['variation_id'] ? '#'.$item['variation_id'] : '').'|FORCE_ITEM_ID|';
						unset($item['item_id']);
						
						$item_to_add = new PHPPOSCartItemSale($item);
						$this->cart->add_item($item_to_add);
					}
					else //Item Kit
					{
						$cur_item_kit_info = $this->Item_kit->get_info($item['item_kit_id']);
						$cur_item_kit_location_info = $this->Item_kit_location->get_info($item['item_kit_id'],$this->cart->location_id);
						
						$item['regular_price'] = ($cur_item_kit_location_info && $cur_item_kit_location_info->unit_price) ? $cur_item_kit_location_info->unit_price : $cur_item_kit_info->unit_price;
						
						$item['scan'] = 'KIT '.$item['item_kit_id'];
						unset($item['item_kit_id']);
						
						$item_kit_to_add = new PHPPOSCartItemKitSale($item);
						$this->cart->add_item_kit($item_kit_to_add);
					}
				}
			}
			
			$this->_populate_custom_fields($sale_request,$this->cart);
			
			$this->cart->skip_webhook = isset($sale_request['skip_webhook']) && $sale_request['skip_webhook'] ? TRUE : FALSE;
						
			$sale_id = $this->Sale->save($this->cart);
			$response = $this->sale_id_to_array($sale_id);
			$this->response($response, REST_Controller::HTTP_OK);
		}
		
		function index_delete($sale_id)
		{
  		$sale = $this->Sale->get_info($sale_id)->row();
  		
  		if ($sale && $sale->sale_id && !$sale->deleted)
  		{
				$this->Sale->delete($sale->sale_id);
				$response = $this->sale_id_to_array($sale->sale_id);
				$this->response($response, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
		
    private function _populate_custom_fields($sale_request,&$cart)
    {
    	$custom_fields_map = array();
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				if($this->Sale->get_custom_field($k) !== false)
				{
					$custom_fields_map[$this->Sale->get_custom_field($k)] = array('index' => $k, 'type' => $this->Sale->get_custom_field($k,'type'));
				}

			}
			if (isset($sale_request['custom_fields']))
			{
				foreach($sale_request['custom_fields'] as $custom_field => $custom_field_value)
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
