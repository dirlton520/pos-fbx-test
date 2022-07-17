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
class Deliveries extends REST_Controller {
	
		protected $methods = [
        'index_get' => ['level' => 1, 'limit' => 20],
        'index_post' => ['level' => 2, 'limit' => 20],
        'index_delete' => ['level' => 2, 'limit' => 20],

      ];

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
				$this->lang->load('deliveries');
				$this->load->model('Delivery');
				$this->load->model('Shipping_provider');
				$this->load->model('Shipping_method');
		
				$this->load->model('Person');
				$this->lang->load('deliveries');
				$this->load->helper('order');
		
				$this->lang->load('module');	
    }
		
		private function _delivery_id_to_array($delivery_id)
		{
			$delivery_info = $this->Delivery->get_info($delivery_id)->row_array();
			$return = array();			
			$return['id'] = $delivery_info['id'];
			$return['sale_id'] = $delivery_info['sale_id'];
			$return['delivery_person_info'] = $this->Delivery->get_delivery_person_info_by_sale_id($return['sale_id']);
			$return['delivery_info'] = $delivery_info;
			$return['delivery_tax_group_id'] = $delivery_info['tax_class_id'];
			return $return;
		}
		
		function index_get($delivery_id = NULL)
		{
			//Search
			if ($delivery_id === NULL)
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
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'estimated_shipping_date';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$deliveries = $this->Delivery->search($search, 0, array(),$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$location_id)->result();
					$total_records = $this->Delivery->search_count_all($search, 0,array(),10000,$location_id);
				}
				else
				{
					
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'estimated_shipping_date';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$deliveries = $this->Delivery->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir, array(),$location_id)->result();
					$total_records = $this->Delivery->count_all(0,$location_id);
				}
				
				$deliveries_return = array();
				foreach($deliveries as $delivery)
				{
						$deliveries_return[] = $this->_delivery_id_to_array($delivery->id);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($deliveries_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}
			else
			{
				if ($this->Delivery->exists($delivery_id))
				{
					$response = $this->_delivery_id_to_array($delivery_id);
					$this->response($response, REST_Controller::HTTP_OK);
				}
				else
				{
					$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
				}
			}
		}
		
		function index_post($delivery_id)
		{
			$delivery_request = json_decode(file_get_contents('php://input'),TRUE);
			
			$delivery_person_data = isset($delivery_request['delivery_person_info']) ? $delivery_request['delivery_person_info'] : FALSE;
			$delivery_data = isset($delivery_request['delivery_info']) ? $delivery_request['delivery_info'] : FALSE;
			
			if (isset($delivery_data['estimated_shipping_date']))
			{
				$delivery_data['estimated_shipping_date'] = date('Y-m-d H:i:s',strtotime($delivery_data['estimated_shipping_date']));
			}
			
			if (isset($delivery_data['actual_shipping_date']))
			{
				$delivery_data['actual_shipping_date'] = date('Y-m-d H:i:s',strtotime($delivery_data['actual_shipping_date']));
			}
			
			
			if (isset($delivery_data['estimated_delivery_or_pickup_date']))
			{
				$delivery_data['estimated_delivery_or_pickup_date'] = date('Y-m-d H:i:s',strtotime($delivery_data['estimated_delivery_or_pickup_date']));
			}
			
			
			if (isset($delivery_data['actual_delivery_or_pickup_date']))
			{
				$delivery_data['actual_delivery_or_pickup_date'] = date('Y-m-d H:i:s',strtotime($delivery_data['actual_delivery_or_pickup_date']));
			}
			
			if (isset($delivery_request['delivery_tax_group_id']))
			{
				$delivery_data['tax_class_id'] = $delivery_request['delivery_tax_group_id'];
			}
			
			if($delivery_data === FALSE || $this->Delivery->save($delivery_data, $delivery_id))
			{
				if ($delivery_person_data !== FALSE)
				{
					$shipping_address_person_id = $this->Delivery->get_info($delivery_id)->row()->shipping_address_person_id;
					$this->Person->save($delivery_person_data,$shipping_address_person_id);
				}
			}
			
			$response = $this->_delivery_id_to_array($delivery_id);
			$this->response($response, REST_Controller::HTTP_OK);
		}
		
		function index_delete($delivery_id)
		{
  		$delivery = $this->Delivery->get_info($delivery_id)->row();
  		
  		if ($delivery && $delivery->id && !$delivery->deleted)
  		{
				$this->Delivery->delete($delivery->id);
				$response = $this->_delivery_id_to_array($delivery->id);
				$this->response($response, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
		
			
}
